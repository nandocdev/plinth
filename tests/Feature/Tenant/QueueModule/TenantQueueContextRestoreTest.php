<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\OperationsContext\QueueModule\Models\TenantQueueContextRun;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper;

function createTenantQueueDomain(string $id): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $id,
      'name' => 'Tenant ' . $id,
      'status' => 'active',
      'region' => 'us-east-1',
      'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => $id . '.localhost',
      'verified_at' => now(),
   ]);

   return $tenant;
}

function workOneTenantQueueJob(): void {
   Artisan::call('queue:work', [
      'connection' => 'database',
      '--queue' => 'default',
      '--once' => true,
      '--tries' => 1,
   ]);
}

beforeEach(function (): void {
   config()->set('queue.default', 'database');
   config()->set('queue.connections.database.connection', 'central');
   config()->set('tenancy.bootstrappers', [
      CacheTenancyBootstrapper::class,
      QueueTenancyBootstrapper::class,
   ]);

   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   if (! Schema::hasTable('tenant_queue_context_runs')) {
      Schema::create('tenant_queue_context_runs', function (Blueprint $table): void {
         $table->id();
         $table->unsignedBigInteger('dispatched_by_user_id')->nullable();
         $table->string('requested_tenant_id');
         $table->string('restored_tenant_id')->nullable();
         $table->string('status', 40);
         $table->text('error_message')->nullable();
         $table->timestamp('processed_at')->nullable();
         $table->timestamps();
      });
   }
});

test('admin puede encolar job tenant y payload guarda tenant_id', function (): void {
   $tenant = createTenantQueueDomain('queue-payload');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      $this->actingAs($admin, 'tenant')
         ->postJson('http://queue-payload.localhost/queue/context-runs')
         ->assertAccepted();
   } finally {
      tenancy()->end();
   }

   $job = DB::connection('central')->table('jobs')->latest('id')->first();
   expect($job)->not->toBeNull();

   /** @var object{payload:string} $job */
   $payload = json_decode($job->payload, true, 512, JSON_THROW_ON_ERROR);
   expect($payload['tenant_id'] ?? null)->toBe($tenant->id);
});

test('worker restaura contexto tenant y marca run procesado', function (): void {
   $tenant = createTenantQueueDomain('queue-restore');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      $response = $this->actingAs($admin, 'tenant')
         ->postJson('http://queue-restore.localhost/queue/context-runs')
         ->assertAccepted()
         ->json();

      $runId = (int) ($response['id'] ?? 0);
      expect($runId)->toBeGreaterThan(0);
   } finally {
      tenancy()->end();
   }

   workOneTenantQueueJob();

   $run = TenantQueueContextRun::query()->find($runId);
   expect($run)->not->toBeNull()
      ->and($run?->requested_tenant_id)->toBe($tenant->id)
      ->and($run?->restored_tenant_id)->toBe($tenant->id)
      ->and($run?->status)->toBe('processed')
      ->and($run?->processed_at)->not->toBeNull();
});

test('member no puede encolar queue runs tenant', function (): void {
   $tenant = createTenantQueueDomain('queue-forbidden');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $member = TenantUser::factory()->create();
      $member->assignRole('member');

      $this->actingAs($member, 'tenant')
         ->postJson('http://queue-forbidden.localhost/queue/context-runs')
         ->assertForbidden();
   } finally {
      tenancy()->end();
   }
});

test('jobs de tenants distintos restauran su contexto sin fugas', function (): void {
   $tenantA = createTenantQueueDomain('queue-iso-a');
   $tenantB = createTenantQueueDomain('queue-iso-b');

   tenancy()->initialize($tenantA);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      $adminA = TenantUser::factory()->create();
      $adminA->assignRole('admin');

      $this->actingAs($adminA, 'tenant')
         ->postJson('http://queue-iso-a.localhost/queue/context-runs')
         ->assertAccepted();
   } finally {
      tenancy()->end();
   }

   tenancy()->initialize($tenantB);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      $adminB = TenantUser::factory()->create();
      $adminB->assignRole('admin');

      $this->actingAs($adminB, 'tenant')
         ->postJson('http://queue-iso-b.localhost/queue/context-runs')
         ->assertAccepted();
   } finally {
      tenancy()->end();
   }

   workOneTenantQueueJob();
   workOneTenantQueueJob();

   $rows = TenantQueueContextRun::query()->orderBy('id')->get();

   expect($rows->count())->toBeGreaterThanOrEqual(2);

   $tenantIds = $rows->pluck('requested_tenant_id')->unique()->values()->all();
   expect($tenantIds)->toContain($tenantA->id)
      ->toContain($tenantB->id);

   foreach ($rows as $row) {
      expect($row->requested_tenant_id)->toBe($row->restored_tenant_id);
   }
});
