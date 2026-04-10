<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\CustomDomainModule\Actions;

use App\Central\TenantProvisioningModule\Models\Domain;
use Illuminate\Support\Collection;

final class ListTenantCustomDomainsAction {
   /**
    * @return Collection<int, Domain>
    */
   public function execute(string $tenantId): Collection {
      return Domain::on('central')
         ->where('tenant_id', $tenantId)
         ->orderByDesc('created_at')
         ->get([
            'id',
            'domain',
            'verified_at',
            'ssl_status',
            'ssl_requested_at',
            'ssl_issued_at',
            'ssl_expires_at',
            'ssl_last_error',
            'created_at',
         ]);
   }
}
