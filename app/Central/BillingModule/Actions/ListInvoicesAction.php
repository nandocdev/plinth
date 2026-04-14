<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\TenantInvoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListInvoicesAction {
   public function execute(string $search = '', int $perPage = 10): LengthAwarePaginator {
      return TenantInvoice::query()
         ->when($search !== '', function ($query) use ($search) {
            $query->where('invoice_number', 'like', "%{$search}%")
               ->orWhere('tenant_id', 'like', "%{$search}%")
               ->orWhere('description', 'like', "%{$search}%");
         })
         ->latest()
         ->paginate($perPage);
   }
}
