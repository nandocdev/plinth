<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\TenantProvisioningRegionData;

final class ListTenantProvisioningRegionsAction {
   /**
    * @return list<TenantProvisioningRegionData>
    */
   public function execute(): array {
      $regions = config('tenancy.multi_region.regions', []);

      if (! is_array($regions) || $regions === []) {
         return [
            new TenantProvisioningRegionData(
               code: (string) config('tenancy.multi_region.default_region', 'default'),
               label: 'Default Region',
               dbConnection: (string) config('tenancy.database.template_tenant_connection', 'tenant_template'),
            ),
         ];
      }

      $result = [];

      foreach ($regions as $code => $settings) {
         if (! is_string($code) || ! is_array($settings)) {
            continue;
         }

         $result[] = new TenantProvisioningRegionData(
            code: $code,
            label: (string) ($settings['label'] ?? strtoupper($code)),
            dbConnection: (string) ($settings['db_connection'] ?? config('tenancy.database.template_tenant_connection', 'tenant_template')),
         );
      }

      return $result;
   }
}
