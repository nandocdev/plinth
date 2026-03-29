<?php

use App\Central\TenantProvisioningModule\Models\Tenant;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

test('aislamiento tenant cache a y b no cruza recursos', function () {
   config()->set('tenancy.bootstrappers', [
      CacheTenancyBootstrapper::class,
   ]);

   /** @var Tenant $tenantA */
   $tenantA = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-isolation-a',
      'data' => ['name' => 'Tenant Isolation A', 'status' => 'active'],
   ]));

   /** @var Tenant $tenantB */
   $tenantB = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-isolation-b',
      'data' => ['name' => 'Tenant Isolation B', 'status' => 'active'],
   ]));

   try {
      tenancy()->initialize($tenantA);
      cache()->put('tenant_resource_key', 'resource-a', 600);
      expect(cache()->get('tenant_resource_key'))->toBe('resource-a');
   } finally {
      tenancy()->end();
   }

   try {
      tenancy()->initialize($tenantB);
      expect(cache()->get('tenant_resource_key'))->toBeNull();
   } finally {
      tenancy()->end();
   }
});
