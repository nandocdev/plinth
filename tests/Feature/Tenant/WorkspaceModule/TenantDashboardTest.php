<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

test('guest es redirigido al login tenant al visitar dashboard', function (): void {
   /** @var \Tests\TestCase $this */
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   createWorkspaceTenantDomain('tenant-dashboard-guest', 'tenant-dashboard-guest.localhost');

   $this->get('http://tenant-dashboard-guest.localhost/dashboard')
      ->assertRedirect('http://tenant-dashboard-guest.localhost/login');
});

test('usuario tenant autenticado ve dashboard basico con welcome y tenant info', function (): void {
   /** @var \Tests\TestCase $this */
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   $tenant = createWorkspaceTenantDomain('tenant-dashboard-auth', 'tenant-dashboard-auth.localhost');

   tenancy()->initialize($tenant);

   try {
      $user = TenantUser::query()->create([
         'name' => 'Workspace Owner',
         'email' => 'owner@tenant-dashboard-auth.test',
         'password' => 'password123',
      ]);

      $this->actingAs($user, 'tenant')
         ->get('http://tenant-dashboard-auth.localhost/dashboard')
         ->assertOk()
         ->assertSee('Bienvenido, Workspace Owner')
         ->assertSee('Tema:')
         ->assertSee('tenant-dashboard-auth.localhost')
         ->assertSee('owner@tenant-dashboard-auth.test')
         ->assertSee('Tenant Dashboard Auth')
         ->assertSee('#123456')
         ->assertSee('#654321');
   } finally {
      tenancy()->end();
   }
});

function createWorkspaceTenantDomain(string $tenantId, string $domain): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $tenantId,
      'name' => 'Tenant ' . $tenantId,
      'status' => 'active',
      'region' => 'us-east-1',
      'branding' => [
         'brand_name' => 'Tenant Dashboard Auth',
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
