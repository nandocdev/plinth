<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\OperationsContext\ActivityLogModule\Livewire\TenantLogsViewer;
use App\Tenant\OperationsContext\ActivityLogModule\Models\TenantActivityLogEntry;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createTenantActivityLogTenant(string $id): Tenant {
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

beforeEach(function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);
});

test('activity log tenant requiere autenticacion', function (): void {
   createTenantActivityLogTenant('tenant-log-guest');

   $this->get('http://tenant-log-guest.localhost/activity-log')
      ->assertRedirect('http://tenant-log-guest.localhost/login');
});

test('usuario no admin recibe 403 en activity log tenant', function (): void {
   $tenant = createTenantActivityLogTenant('tenant-log-member');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $member = TenantUser::factory()->create();
      $member->assignRole('member');

      $this->actingAs($member, 'tenant')
         ->get('http://tenant-log-member.localhost/activity-log')
         ->assertForbidden();
   } finally {
      tenancy()->end();
   }
});

test('admin ve solo logs de su tenant filtrados por tenant_id', function (): void {
   $tenantA = createTenantActivityLogTenant('tenant-log-a');
   $tenantB = createTenantActivityLogTenant('tenant-log-b');

   tenancy()->initialize($tenantA);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $adminA = TenantUser::factory()->create(['email' => 'admin@tenant-log-a.test']);
      $adminA->assignRole('admin');

      activity('tenant_audit')
         ->causedBy($adminA)
         ->withProperties(['tenant_id' => $tenantA->id, 'route_name' => 'tenant.dashboard'])
         ->tap(function (\Spatie\Activitylog\Models\Activity $activity) use ($tenantA): void {
            $activity->event = 'get.request';
            $activity->tenant_id = $tenantA->id;
         })
         ->log('Tenant A action');

      activity('tenant_audit')
         ->causedBy($adminA)
         ->withProperties(['tenant_id' => $tenantB->id, 'route_name' => 'tenant.dashboard'])
         ->tap(function (\Spatie\Activitylog\Models\Activity $activity) use ($tenantB): void {
            $activity->event = 'get.request';
            $activity->tenant_id = $tenantB->id;
         })
         ->log('Tenant B action');

      Livewire::actingAs($adminA, 'tenant')
         ->test(TenantLogsViewer::class)
         ->assertSee('Tenant A action')
         ->assertDontSee('Tenant B action')
         ->set('filterForm.search', 'Tenant A')
         ->assertSee('Tenant A action');
   } finally {
      tenancy()->end();
   }
});

test('middleware tenant.audit registra request con tenant_id', function (): void {
   $tenant = createTenantActivityLogTenant('tenant-log-middleware');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      $this->actingAs($admin, 'tenant')
         ->get('http://tenant-log-middleware.localhost/activity-log')
         ->assertOk();

      $entry = TenantActivityLogEntry::query()
         ->where('tenant_id', $tenant->id)
         ->latest('id')
         ->first();

      expect($entry)->not->toBeNull()
         ->and($entry?->event)->toBe('get.request')
         ->and((string) data_get($entry?->properties, 'tenant_id'))->toBe($tenant->id);
   } finally {
      tenancy()->end();
   }
});
