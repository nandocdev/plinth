<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\FileUploadModule\Livewire\TenantFileUploads;
use App\Tenant\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createFileUploadTenant(string $id): Tenant {
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
   Storage::fake('tenant');
});

test('guest es redirigido al login en files tenant', function (): void {
   createFileUploadTenant('files-guest');

   $this->get('http://files-guest.localhost/files')
      ->assertRedirect('http://files-guest.localhost/login');
});

test('admin puede subir archivo seguro en tenant disk', function (): void {
   $tenant = createFileUploadTenant('files-admin');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      $file = UploadedFile::fake()->create('contrato.pdf', 120, 'application/pdf');

      Livewire::actingAs($admin, 'tenant')
         ->test(TenantFileUploads::class)
         ->set('form.file', $file)
         ->set('form.folder', 'contratos')
         ->call('upload')
         ->assertHasNoErrors()
         ->assertSet('successMessage', 'Archivo subido correctamente.');

      $row = DB::connection('central')->table('tenant_uploaded_files')->where('tenant_id', $tenant->id)->latest('id')->first();

      expect($row)->not->toBeNull()
         ->and($row?->disk)->toBe('tenant')
         ->and($row?->original_name)->toBe('contrato.pdf');

      Storage::disk('tenant')->assertExists((string) $row?->stored_path);
   } finally {
      tenancy()->end();
   }
});

test('usuario member no puede subir archivos', function (): void {
   $tenant = createFileUploadTenant('files-member');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $member = TenantUser::factory()->create();
      $member->assignRole('member');

      $this->actingAs($member, 'tenant')
         ->get('http://files-member.localhost/files')
         ->assertForbidden();
   } finally {
      tenancy()->end();
   }
});

test('listado solo muestra archivos del tenant actual', function (): void {
   $tenantA = createFileUploadTenant('files-iso-a');
   $tenantB = createFileUploadTenant('files-iso-b');

   DB::connection('central')->table('tenant_uploaded_files')->insert([
      [
         'tenant_id' => $tenantA->id,
         'uploaded_by_user_id' => null,
         'disk' => 'tenant',
         'folder' => 'docs',
         'original_name' => 'a.pdf',
         'stored_name' => 'a.pdf',
         'stored_path' => 'docs/a.pdf',
         'mime_type' => 'application/pdf',
         'size_bytes' => 100,
         'created_at' => now(),
         'updated_at' => now(),
      ],
      [
         'tenant_id' => $tenantB->id,
         'uploaded_by_user_id' => null,
         'disk' => 'tenant',
         'folder' => 'docs',
         'original_name' => 'b.pdf',
         'stored_name' => 'b.pdf',
         'stored_path' => 'docs/b.pdf',
         'mime_type' => 'application/pdf',
         'size_bytes' => 100,
         'created_at' => now(),
         'updated_at' => now(),
      ],
   ]);

   tenancy()->initialize($tenantA);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      $adminA = TenantUser::factory()->create();
      $adminA->assignRole('admin');

      Livewire::actingAs($adminA, 'tenant')
         ->test(TenantFileUploads::class)
         ->assertSee('a.pdf')
         ->assertDontSee('b.pdf');
   } finally {
      tenancy()->end();
   }
});
