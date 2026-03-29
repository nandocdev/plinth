<?php

use App\Central\TenantProvisioningModule\Actions\ListTenantProvisioningRegionsAction;
use App\Central\TenantProvisioningModule\Actions\ResolveTenantProvisioningRegionAction;
use App\Central\TenantProvisioningModule\DTOs\CreateTenantData;
use Illuminate\Support\Facades\Config;

test('lista regiones de provisioning configuradas para tenancy', function () {
   Config::set('tenancy.multi_region.default_region', 'us-east-1');
   Config::set('tenancy.multi_region.regions', [
      'us-east-1' => [
         'label' => 'US East',
         'db_connection' => 'tenant_template_us_east_1',
      ],
      'eu-west-1' => [
         'label' => 'EU West',
         'db_connection' => 'tenant_template_eu_west_1',
      ],
   ]);

   $regions = app(ListTenantProvisioningRegionsAction::class)->execute();

   expect($regions)->toHaveCount(2)
      ->and($regions[0]->code)->toBe('us-east-1')
      ->and($regions[1]->code)->toBe('eu-west-1');
});

test('resuelve region solicitada o aplica fallback de default region', function () {
   Config::set('tenancy.multi_region.default_region', 'eu-west-1');
   Config::set('tenancy.multi_region.regions', [
      'us-east-1' => [
         'label' => 'US East',
         'db_connection' => 'tenant_template_us_east_1',
      ],
      'eu-west-1' => [
         'label' => 'EU West',
         'db_connection' => 'tenant_template_eu_west_1',
      ],
   ]);

   $resolver = app(ResolveTenantProvisioningRegionAction::class);

   $explicit = $resolver->execute('us-east-1');
   $fallback = $resolver->execute('ap-south-1');

   expect($explicit->dbConnection)->toBe('tenant_template_us_east_1')
      ->and($fallback->code)->toBe('eu-west-1');
});

test('dto de creacion de tenant conserva region seleccionada', function () {
   $dto = CreateTenantData::fromValues('Acme', 'acme.localhost', 'eu-west-1');

   expect($dto->region)->toBe('eu-west-1')
      ->and($dto->name)->toBe('Acme')
      ->and($dto->primaryDomain)->toBe('acme.localhost');
});
