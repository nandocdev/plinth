<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\WorkspaceModule\Livewire\TenantProfile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createProfileTenant(string $id): Tenant {
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

test('guest es redirigido al login al visitar perfil', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   createProfileTenant('profile-guest');

   $this->get('http://profile-guest.localhost/profile')
      ->assertRedirect('http://profile-guest.localhost/login');
});

test('usuario autenticado ve el formulario de perfil con sus datos', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-render');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::factory()->create([
         'name' => 'Ana Garcia',
         'email' => 'ana@profile-render.test',
      ]);

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->assertOk()
         ->assertSet('updateProfileForm.name', 'Ana Garcia')
         ->assertSet('updateProfileForm.email', 'ana@profile-render.test');
   } finally {
      tenancy()->end();
   }
});

test('usuario puede actualizar nombre y email correctamente', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-update-ok');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::factory()->create([
         'name' => 'Nombre Original',
         'email' => 'original@profile-update-ok.test',
      ]);

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->set('updateProfileForm.name', 'Nombre Actualizado')
         ->set('updateProfileForm.email', 'nuevo@profile-update-ok.test')
         ->call('updateProfile')
         ->assertHasNoErrors()
         ->assertSet('profileSuccess', 'Perfil actualizado correctamente.');

      $updated = $user->fresh();
      expect($updated->name)->toBe('Nombre Actualizado');
      expect($updated->email)->toBe('nuevo@profile-update-ok.test');
      expect($updated->email_verified_at)->toBeNull();
   } finally {
      tenancy()->end();
   }
});

test('actualizar perfil falla si el email ya esta en uso por otro usuario', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-email-dup');

   tenancy()->initialize($tenant);

   try {
      TenantUser::factory()->create(['email' => 'taken@profile-email-dup.test']);
      $user = TenantUser::factory()->create(['email' => 'mine@profile-email-dup.test']);

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->set('updateProfileForm.name', 'Test')
         ->set('updateProfileForm.email', 'taken@profile-email-dup.test')
         ->call('updateProfile')
         ->assertHasErrors(['updateProfileForm.email']);
   } finally {
      tenancy()->end();
   }
});

test('actualizar perfil falla si el nombre esta vacio', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-name-empty');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::factory()->create();

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->set('updateProfileForm.name', '')
         ->call('updateProfile')
         ->assertHasErrors(['updateProfileForm.name']);
   } finally {
      tenancy()->end();
   }
});

test('usuario puede cambiar contrasena con contrasena actual correcta', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-pwd-ok');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::factory()->create(['password' => Hash::make('OldPass123!')]);

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->set('updatePasswordForm.current_password', 'OldPass123!')
         ->set('updatePasswordForm.password', 'NewPass456!')
         ->set('updatePasswordForm.password_confirmation', 'NewPass456!')
         ->call('updatePassword')
         ->assertHasNoErrors()
         ->assertSet('passwordSuccess', 'Contraseña actualizada correctamente.');

      expect(Hash::check('NewPass456!', $user->fresh()->password))->toBeTrue();
   } finally {
      tenancy()->end();
   }
});

test('cambio de contrasena falla si la contrasena actual es incorrecta', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-pwd-wrong');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::factory()->create(['password' => Hash::make('RealPass123!')]);

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->set('updatePasswordForm.current_password', 'WrongPassword!')
         ->set('updatePasswordForm.password', 'NewPass456!')
         ->set('updatePasswordForm.password_confirmation', 'NewPass456!')
         ->call('updatePassword')
         ->assertHasErrors(['updatePasswordForm.current_password']);

      expect(Hash::check('RealPass123!', $user->fresh()->password))->toBeTrue();
   } finally {
      tenancy()->end();
   }
});

test('cambio de contrasena falla si la confirmacion no coincide', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-pwd-confirm');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::factory()->create(['password' => Hash::make('OldPass123!')]);

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->set('updatePasswordForm.current_password', 'OldPass123!')
         ->set('updatePasswordForm.password', 'NewPass456!')
         ->set('updatePasswordForm.password_confirmation', 'DifferentPass!')
         ->call('updatePassword')
         ->assertHasErrors(['updatePasswordForm.password']);
   } finally {
      tenancy()->end();
   }
});

test('componente de perfil solo expone datos del usuario autenticado', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenantA = createProfileTenant('profile-iso-a');
   $tenantB = createProfileTenant('profile-iso-b');

   tenancy()->initialize($tenantA);
   $userA = TenantUser::factory()->create(['email' => 'userA@iso-a.test']);
   tenancy()->end();

   tenancy()->initialize($tenantB);
   TenantUser::factory()->create(['email' => 'userB@iso-b.test']);
   tenancy()->end();

   tenancy()->initialize($tenantA);

   try {
      $component = Livewire::actingAs($userA, 'tenant')
         ->test(TenantProfile::class);

      $component->assertSet('updateProfileForm.email', 'userA@iso-a.test');
      expect($component->get('currentUser')->email)->toBe('userA@iso-a.test');
   } finally {
      tenancy()->end();
   }
});

test('usuario tenant puede iniciar y desactivar 2fa cuando lo desee', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createProfileTenant('profile-two-factor-optional');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::factory()->create();

      Livewire::actingAs($user, 'tenant')
         ->test(TenantProfile::class)
         ->call('enableTwoFactor')
         ->assertHasNoErrors()
         ->assertSet('twoFactorSuccess', '2FA iniciado. Escanea el QR y confirma con tu código.');

      $enabledUser = $user->fresh();
      expect($enabledUser->two_factor_secret)->not->toBeNull();

      Livewire::actingAs($enabledUser, 'tenant')
         ->test(TenantProfile::class)
         ->call('disableTwoFactor')
         ->assertHasNoErrors()
         ->assertSet('twoFactorSuccess', '2FA deshabilitado.');

      $disabledUser = $enabledUser->fresh();
      expect($disabledUser->two_factor_secret)->toBeNull()
         ->and($disabledUser->two_factor_recovery_codes)->toBeNull()
         ->and($disabledUser->two_factor_confirmed_at)->toBeNull();
   } finally {
      tenancy()->end();
   }
});
