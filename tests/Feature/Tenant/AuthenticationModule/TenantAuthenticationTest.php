<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Livewire\TenantLogin;
use App\Tenant\IdentityContext\AuthenticationModule\Livewire\TenantRegister;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Livewire\Livewire;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

test('tenant login page se renderiza en dominio tenant', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   createTenantAuthDomain('tenant-auth-login-page', 'tenant-auth-login.localhost');

   $this->get('http://tenant-auth-login.localhost/login')
      ->assertOk()
      ->assertSee('Acceso Tenant');
});

test('tenant register page se renderiza en dominio tenant', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   createTenantAuthDomain('tenant-auth-register-page', 'tenant-auth-register.localhost');

   $this->get('http://tenant-auth-register.localhost/register')
      ->assertOk()
      ->assertSee('Registro Tenant');
});

test('tenant register crea usuario aislado y autentica con guard tenant', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenant = createTenantAuthTenant('tenant-auth-register');

   tenancy()->initialize($tenant);

   try {
      Livewire::test(TenantRegister::class)
         ->set('form.name', 'Tenant Register User')
         ->set('form.email', 'register@tenant-auth.test')
         ->set('form.password', 'password123')
         ->set('form.passwordConfirmation', 'password123')
         ->call('register')
         ->assertHasNoErrors()
         ->assertRedirect('/dashboard');

      $createdUser = TenantUser::query()->where('email', 'register@tenant-auth.test')->first();

      expect($createdUser)->not->toBeNull();
      $this->assertAuthenticated('tenant');
   } finally {
      tenancy()->end();
   }
});

test('tenant login autentica usuario existente con guard tenant', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenant = createTenantAuthTenant('tenant-auth-login');

   tenancy()->initialize($tenant);

   try {
      TenantUser::query()->create([
         'name' => 'Tenant Login User',
         'email' => 'login@tenant-auth.test',
         'password' => 'password123',
      ]);

      Livewire::test(TenantLogin::class)
         ->set('form.email', 'login@tenant-auth.test')
         ->set('form.password', 'password123')
         ->set('form.remember', true)
         ->call('login')
         ->assertHasNoErrors()
         ->assertRedirect('/dashboard');

      $this->assertAuthenticated('tenant');
   } finally {
      tenancy()->end();
   }
});

test('guest en portal protegido tenant redirige al login tenant', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   createTenantAuthDomain('tenant-auth-protected-redirect', 'tenant-auth-protected.localhost');

   $this->get('http://tenant-auth-protected.localhost/billing')
      ->assertRedirect('http://tenant-auth-protected.localhost/login');
});

function createTenantAuthDomain(string $tenantId, string $domain): Tenant {
   $tenant = createTenantAuthTenant($tenantId);

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => $domain,
      'verified_at' => now(),
   ]);

   return $tenant;
}

function createTenantAuthTenant(string $tenantId): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $tenantId,
      'name' => 'Tenant ' . $tenantId,
      'status' => 'active',
      'region' => 'us-east-1',
      'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $tenantId),
   ]));

   return $tenant;
}
