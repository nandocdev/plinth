<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\Models\Tenant;

final class ListTenantBrandingOptionsAction {
   /**
    * @return list<array{id: string, name: string}>
    */
   public function execute(): array {
      return Tenant::query()
         ->orderBy('created_at')
         ->get(['id', 'data'])
         ->map(static fn(Tenant $tenant): array => [
            'id' => (string) $tenant->id,
            'name' => $tenant->displayName(),
         ])
         ->all();
   }
}
