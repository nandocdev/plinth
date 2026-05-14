<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createTenantForErrorHandling(string $tenantId, string $domain, string $status = 'active'): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $tenantId,
      'name' => 'Tenant ' . $tenantId,
      'status' => $status,
      'region' => 'us-east-1',
      'branding' => [
         'brand_name' => 'Workspace ' . $tenantId,
      ],
      'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $tenantId),
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => $domain,
      'verified_at' => now(),
   ]);

   return $tenant;
}

function createTenantUser(Tenant $tenant, string $email): TenantUser {
   tenancy()->initialize($tenant);

   try {
      /** @var TenantUser $user */
      $user = TenantUser::query()->create([
         'name' => 'Error Tester',
         'email' => $email,
         'password' => 'password123',
      ]);
   } finally {
      tenancy()->end();
   }

   return $user;
}

test('tenant en maintenance devuelve pagina 503 aislada por tenant', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenant = createTenantForErrorHandling('tenant-maintenance-a', 'tenant-maintenance-a.localhost', 'maintenance');
   $user = createTenantUser($tenant, 'maintenance-a@test.local');

   $this->actingAs($user, 'tenant')
      ->get('http://tenant-maintenance-a.localhost/dashboard')
      ->assertStatus(503)
      ->assertSee('Workspace en mantenimiento')
      ->assertSee('Workspace tenant-maintenance-a')
      ->assertSee('maintenance');
});

test('tenant activo no entra en maintenance y sigue flujo normal', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   createTenantForErrorHandling('tenant-active-b', 'tenant-active-b.localhost', 'active');

   // Guest debería ir a login, no a maintenance.
   $this->get('http://tenant-active-b.localhost/dashboard')
      ->assertRedirect('http://tenant-active-b.localhost/login');
});

test('ruta inexistente en tenant renderiza 404 tenant-isolated', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   createTenantForErrorHandling('tenant-404-c', 'tenant-404-c.localhost', 'active');

   $this->get('http://tenant-404-c.localhost/recurso-que-no-existe')
      ->assertStatus(404)
      ->assertSee('Página no encontrada en este workspace')
      ->assertSee('Workspace tenant-404-c')
      ->assertSee('tenant-404-c');
});

test('maintenance es aislado entre tenants', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);

   $tenantA = createTenantForErrorHandling('tenant-maint-a', 'tenant-maint-a.localhost', 'maintenance');
   $tenantB = createTenantForErrorHandling('tenant-maint-b', 'tenant-maint-b.localhost', 'active');
   $userA = createTenantUser($tenantA, 'maintenance-a2@test.local');

   $this->actingAs($userA, 'tenant')
      ->get('http://tenant-maint-a.localhost/dashboard')
      ->assertStatus(503)
      ->assertSee('Workspace en mantenimiento');

   $this->get('http://tenant-maint-b.localhost/dashboard')
      ->assertOk()
      ->assertDontSee('Workspace en mantenimiento');
});
