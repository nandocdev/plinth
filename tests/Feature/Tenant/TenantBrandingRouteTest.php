<?php

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\GovernanceContext\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;

test('home tenant refleja branding configurado desde contexto central', function () {
   config()->set('tenancy.bootstrappers', []);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-brand-route',
      'name' => 'Brand Route Tenant',
      'status' => 'active',
      'branding' => [
         'brand_name' => 'Orbit Workspace',
         'primary_color' => '#123456',
         'secondary_color' => '#654321',
         'logo_url' => 'https://cdn.example.com/orbit.svg',
      ],
      'tenancy_db_name' => 'tenant_brand_route',
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => 'orbit.localhost',
      'verified_at' => now(),
   ]);

   $response = $this->get('http://orbit.localhost/');

   $response->assertOk()
      ->assertSee('Orbit Workspace')
      ->assertSee('#123456')
      ->assertSee('#654321');
});
