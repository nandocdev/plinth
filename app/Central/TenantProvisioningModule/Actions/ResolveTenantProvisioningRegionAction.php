<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\TenantProvisioningRegionData;

final class ResolveTenantProvisioningRegionAction {
   public function __construct(
      private readonly ListTenantProvisioningRegionsAction $listRegions,
   ) {
   }

   public function execute(?string $regionCode): TenantProvisioningRegionData {
      $regions = $this->listRegions->execute();

      if ($regions === []) {
         return new TenantProvisioningRegionData(
            code: 'default',
            label: 'Default Region',
            dbConnection: (string) config('tenancy.database.template_tenant_connection', 'tenant_template'),
         );
      }

      $requested = is_string($regionCode) && $regionCode !== ''
         ? $regionCode
         : (string) config('tenancy.multi_region.default_region', $regions[0]->code);

      foreach ($regions as $region) {
         if ($region->code === $requested) {
            return $region;
         }
      }

      $defaultRegion = (string) config('tenancy.multi_region.default_region', $regions[0]->code);

      foreach ($regions as $region) {
         if ($region->code === $defaultRegion) {
            return $region;
         }
      }

      return $regions[0];
   }
}
