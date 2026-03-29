<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Actions;

use App\Central\TenantProvisioningModule\Models\Tenant;

final class ListTenantLogFilterOptionsAction {
   /**
    * @return array<int, array{id: string, name: string}>
    */
   public function execute(): array {
      return Tenant::query()
         ->select(['id', 'data'])
         ->orderBy('id')
         ->limit(200)
         ->get()
         ->map(function (Tenant $tenant): array {
            return [
               'id' => (string) $tenant->id,
               'name' => $tenant->displayName(),
            ];
         })
         ->values()
         ->all();
   }
}
