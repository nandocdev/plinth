<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\PlatformContext\ExportImportModule\Actions\QueueTenantCsvImportAction;
use App\Tenant\PlatformContext\ExportImportModule\DTOs\QueueTenantCsvImportData;
use App\Tenant\PlatformContext\ExportImportModule\Livewire\TenantCsvTransferCenter;
use App\Tenant\PlatformContext\ExportImportModule\Models\TenantCsvTransferRun;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper;

function createCsvTransferTenant(string $id): Tenant {
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

function workOneTenantCsvJob(): void {
   Artisan::call('queue:work', [
      'connection' => 'database',
      '--queue' => 'exports,imports',
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
   Storage::fake('tenant');
});

test('admin puede encolar export csv tenant-aware y worker genera archivo', function (): void {
   $tenant = createCsvTransferTenant('csv-export-a');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      TenantUser::factory()->create(['email' => 'export-user-a@test.local']);

      Livewire::actingAs($admin, 'tenant')
         ->test(TenantCsvTransferCenter::class)
         ->call('queueExport')
         ->assertSet('successMessage', 'Export CSV encolado correctamente.');
   } finally {
      tenancy()->end();
   }

   workOneTenantCsvJob();

   $run = TenantCsvTransferRun::on('central')->where('tenant_id', $tenant->id)->where('type', 'export')->latest('id')->first();

   expect($run)->not->toBeNull()
      ->and($run?->status)->toBe('completed')
      ->and($run?->result_path)->not->toBeNull();

   $absolutePath = storage_path('app/private/' . (string) $run?->result_path);
   expect(file_exists($absolutePath))->toBeTrue()
      ->and(file_get_contents($absolutePath) ?: '')->toContain('export-user-a@test.local');
});

test('import csv crea usuarios en tenant actual', function (): void {
   $tenant = createCsvTransferTenant('csv-import-a');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      $csvPath = 'csv/imports/users.csv';
      Storage::disk('tenant')->put($csvPath, "name,email\nAlice Import,alice-import@test.local\n");

      app(QueueTenantCsvImportAction::class)->execute(new QueueTenantCsvImportData(
         tenantId: (string) $tenant->id,
         requestedByUserId: $admin->id,
         sourceDisk: 'tenant',
         sourcePath: $csvPath,
      ));
   } finally {
      tenancy()->end();
   }

   workOneTenantCsvJob();

   tenancy()->initialize($tenant);

   try {
      expect(TenantUser::query()->where('email', 'alice-import@test.local')->exists())->toBeTrue();
   } finally {
      tenancy()->end();
   }
});

test('aislamiento tenant: cada job export restaura el contexto correcto', function (): void {
   $tenantA = createCsvTransferTenant('csv-iso-a');
   $tenantB = createCsvTransferTenant('csv-iso-b');

   tenancy()->initialize($tenantA);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $adminA = TenantUser::factory()->create();
      $adminA->assignRole('admin');
      TenantUser::factory()->create(['email' => 'only-a@test.local']);

      Livewire::actingAs($adminA, 'tenant')
         ->test(TenantCsvTransferCenter::class)
         ->call('queueExport');
   } finally {
      tenancy()->end();
   }

   tenancy()->initialize($tenantB);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $adminB = TenantUser::factory()->create();
      $adminB->assignRole('admin');
      TenantUser::factory()->create(['email' => 'only-b@test.local']);

      Livewire::actingAs($adminB, 'tenant')
         ->test(TenantCsvTransferCenter::class)
         ->call('queueExport');
   } finally {
      tenancy()->end();
   }

   workOneTenantCsvJob();
   workOneTenantCsvJob();

   $runA = TenantCsvTransferRun::on('central')->where('tenant_id', $tenantA->id)->where('type', 'export')->latest('id')->first();
   $runB = TenantCsvTransferRun::on('central')->where('tenant_id', $tenantB->id)->where('type', 'export')->latest('id')->first();

   expect(data_get($runA?->meta, 'restored_tenant_id'))->toBe($tenantA->id);
   expect(data_get($runB?->meta, 'restored_tenant_id'))->toBe($tenantB->id);
});

test('member no puede acceder a pantalla CSV transfer', function (): void {
   $tenant = createCsvTransferTenant('csv-forbidden');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $member = TenantUser::factory()->create();
      $member->assignRole('member');

      $this->actingAs($member, 'tenant')
         ->get('http://csv-forbidden.localhost/csv-transfer')
         ->assertForbidden();
   } finally {
      tenancy()->end();
   }
});

test('job import marca failed cuando el archivo csv no existe', function (): void {
   $tenant = createCsvTransferTenant('csv-failure');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      app(QueueTenantCsvImportAction::class)->execute(new QueueTenantCsvImportData(
         tenantId: (string) $tenant->id,
         requestedByUserId: $admin->id,
         sourceDisk: 'tenant',
         sourcePath: 'csv/imports/missing-file.csv',
      ));
   } finally {
      tenancy()->end();
   }

   workOneTenantCsvJob();

   $run = TenantCsvTransferRun::on('central')->where('tenant_id', $tenant->id)->where('type', 'import')->latest('id')->first();

   expect($run)->not->toBeNull()
      ->and($run?->status)->toBe('failed')
      ->and((string) $run?->error_message)->toContain('No existe el archivo CSV');
});
