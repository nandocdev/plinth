<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\GovernanceContext\SettingsModule\Actions\GetTenantSettingsAction;
use App\Tenant\GovernanceContext\SettingsModule\Livewire\TenantSettings;
use App\Tenant\IdentityContext\UserManagementModule\Actions\SeedDefaultRolesAction;
use Livewire\Livewire;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createSettingsTenant(string $id): Tenant {
   DB::connection('central')->table('tenants')->insert([
      'id' => $id,
      'data' => json_encode([
         'name' => 'Tenant ' . $id,
         'status' => 'active',
         'region' => 'us-east-1',
         'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
      ], JSON_THROW_ON_ERROR),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   DB::connection('central')->table('domains')->insert([
      'tenant_id' => $id,
      'domain' => $id . '.localhost',
      'verified_at' => now(),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   /** @var Tenant $tenant */
   $tenant = Tenant::query()->findOrFail($id);

   return $tenant;
}

beforeEach(function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);
});

test('guest es redirigido al login al visitar settings tenant', function (): void {
   createSettingsTenant('settings-guest');

   $this->get('http://settings-guest.localhost/settings/tenant')
      ->assertRedirect('http://settings-guest.localhost/login');
});

test('admin puede ver y actualizar configuracion tenant', function (): void {
   $tenant = createSettingsTenant('settings-admin');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create(['email' => 'admin@settings-admin.test']);
      $admin->assignRole('admin');

      $initial = app(GetTenantSettingsAction::class)->execute();
      expect($initial->companyName)->toBe('Mi Empresa');

      Livewire::actingAs($admin, 'tenant')
         ->test(TenantSettings::class)
         ->assertOk()
         ->set('form.companyName', 'Orbit Labs')
         ->set('form.legalName', 'Orbit Labs LLC')
         ->set('form.supportEmail', 'support@orbit.test')
         ->set('form.brandName', 'Orbit')
         ->set('form.logoUrl', 'https://cdn.example.com/orbit.svg')
         ->set('form.primaryColor', '#111827')
         ->set('form.secondaryColor', '#0ea5e9')
         ->set('form.locale', 'en')
         ->set('form.timezone', 'America/Bogota')
         ->set('form.currency', 'COP')
         ->set('form.allowWeeklyDigest', false)
         ->set('form.dateFormat', 'Y-m-d')
         ->call('save')
         ->assertHasNoErrors()
         ->assertSet('successMessage', 'Configuración actualizada correctamente.');

      $updated = app(GetTenantSettingsAction::class)->execute();
      expect($updated->companyName)->toBe('Orbit Labs')
         ->and($updated->supportEmail)->toBe('support@orbit.test')
         ->and($updated->brandName)->toBe('Orbit')
         ->and($updated->locale)->toBe('en')
         ->and($updated->currency)->toBe('COP')
         ->and($updated->allowWeeklyDigest)->toBeFalse()
         ->and($updated->dateFormat)->toBe('Y-m-d');
   } finally {
      tenancy()->end();
   }
});

test('usuario no admin recibe 403 en settings tenant', function (): void {
   $tenant = createSettingsTenant('settings-member');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $member = TenantUser::factory()->create();
      $member->assignRole('member');
   } finally {
      tenancy()->end();
   }

   $this->actingAs($member, 'tenant')
      ->get('http://settings-member.localhost/settings/tenant')
      ->assertForbidden();
});

test('primer usuario sin roles recibe bootstrap admin en settings tenant', function (): void {
   $tenant = createSettingsTenant('settings-bootstrap-owner');

   tenancy()->initialize($tenant);

   try {
      $owner = TenantUser::factory()->create(['email' => 'owner@settings-bootstrap-owner.test']);
   } finally {
      tenancy()->end();
   }

   $this->actingAs($owner, 'tenant')
      ->get('http://settings-bootstrap-owner.localhost/settings/tenant')
      ->assertOk();

   tenancy()->initialize($tenant);

   try {
      $owner->refresh();

      expect($owner->hasRole('admin', 'tenant'))->toBeTrue();
   } finally {
      tenancy()->end();
   }
});
