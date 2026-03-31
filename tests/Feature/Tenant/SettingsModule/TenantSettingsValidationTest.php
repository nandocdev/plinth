<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\SettingsModule\Livewire\TenantSettings;
use App\Tenant\UserManagementModule\Actions\SeedDefaultRolesAction;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createSettingsValidationTenant(string $id): Tenant {
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

test('valida formato de branding, correo y timezone', function (): void {
   $tenant = createSettingsValidationTenant('settings-validation');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create();
      $admin->assignRole('admin');

      Livewire::actingAs($admin, 'tenant')
         ->test(TenantSettings::class)
         ->set('form.companyName', '')
         ->set('form.supportEmail', 'not-an-email')
         ->set('form.primaryColor', 'blue')
         ->set('form.secondaryColor', '#123')
         ->set('form.timezone', 'Invalid/Timezone')
         ->set('form.currency', 'US')
         ->set('form.dateFormat', 'DD-MM-YYYY')
         ->call('save')
         ->assertHasErrors([
            'form.companyName',
            'form.supportEmail',
            'form.primaryColor',
            'form.secondaryColor',
            'form.timezone',
            'form.currency',
            'form.dateFormat',
         ]);
   } finally {
      tenancy()->end();
   }
});
