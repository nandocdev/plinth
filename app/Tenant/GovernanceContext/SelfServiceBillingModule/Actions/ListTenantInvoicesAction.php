<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions;

use App\Central\BillingModule\Models\TenantInvoice;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListTenantInvoicesAction {
   /**
    * Lista facturas del tenant desde la BD central.
    *
    * @return LengthAwarePaginator<TenantInvoice>
    */
   public function execute(string $tenantId, int $perPage = 15): LengthAwarePaginator {
      return TenantInvoice::query()
         ->where('tenant_id', $tenantId)
         ->orderByDesc('created_at')
         ->paginate($perPage);
   }
}
