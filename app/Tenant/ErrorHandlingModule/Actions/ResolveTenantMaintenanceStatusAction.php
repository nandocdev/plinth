<?php

declare(strict_types=1);

namespace App\Tenant\ErrorHandlingModule\Actions;

use App\Tenant\ErrorHandlingModule\DTOs\TenantMaintenanceStatusData;

final class ResolveTenantMaintenanceStatusAction {
   public function execute(): TenantMaintenanceStatusData {
      $tenant = tenancy()->tenant;

      if ($tenant === null) {
         return new TenantMaintenanceStatusData(
            isInMaintenance: false,
            tenantId: '',
            tenantName: config('app.name', 'Workspace'),
         );
      }

      $status = strtolower((string) data_get($tenant, 'status', 'active'));
      $isInMaintenance = in_array($status, ['maintenance', 'maintenance_mode'], true);

      return new TenantMaintenanceStatusData(
         isInMaintenance: $isInMaintenance,
         tenantId: (string) data_get($tenant, 'id', ''),
         tenantName: method_exists($tenant, 'brandName') ? (string) $tenant->brandName() : (string) data_get($tenant, 'id', 'Workspace'),
         message: $isInMaintenance ? 'Este workspace está temporalmente en mantenimiento.' : null,
      );
   }
}
