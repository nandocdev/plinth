<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Actions;

use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Collection;

final class ListCentralExportTenantOptionsAction {
   /**
    * @return Collection<int, array{id: string, name: string}>
    */
   public function execute(): Collection {
      return Tenant::query()
         ->latest('created_at')
         ->get(['id', 'data'])
         ->map(function (Tenant $tenant): array {
            return [
               'id' => $tenant->id,
               'name' => $tenant->displayName(),
            ];
         })
         ->values();
   }
}
