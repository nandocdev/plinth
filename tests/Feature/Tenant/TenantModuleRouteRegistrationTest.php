<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

test('rutas tenant de los modulos se registran en dominio tenant', function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   createTenantRouteDomain('tenant-routes-module', 'tenant-routes-module.localhost');

   $this->get('http://tenant-routes-module.localhost/')
      ->assertOk()
      ->assertSee('Tenant Routes Module');

   $this->get('http://tenant-routes-module.localhost/profile')
      ->assertRedirect('http://tenant-routes-module.localhost/login');

   $this->get('http://tenant-routes-module.localhost/billing')
      ->assertRedirect('http://tenant-routes-module.localhost/login');

   $this->get('http://tenant-routes-module.localhost/impersonation/leave')
      ->assertRedirect('/?impersonated=0');
});

test('dominios centrales no exponen rutas tenant registradas por modulos', function (): void {
   $this->get('http://localhost/profile')->assertNotFound();
   $this->get('http://localhost/billing')->assertNotFound();
   $this->get('http://localhost/impersonation/leave')->assertNotFound();
});

function createTenantRouteDomain(string $tenantId, string $domain): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $tenantId,
      'name' => 'Tenant ' . $tenantId,
      'status' => 'active',
      'region' => 'us-east-1',
      'branding' => [
         'brand_name' => 'Tenant Routes Module',
         'primary_color' => '#123456',
         'secondary_color' => '#654321',
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
