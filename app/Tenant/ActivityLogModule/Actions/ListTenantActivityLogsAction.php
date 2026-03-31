<?php

declare(strict_types=1);

namespace App\Tenant\ActivityLogModule\Actions;

use App\Tenant\ActivityLogModule\DTOs\ListTenantLogsFilterData;
use App\Tenant\ActivityLogModule\Models\TenantActivityLogEntry;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListTenantActivityLogsAction {
   public function execute(ListTenantLogsFilterData $filter): LengthAwarePaginator {
      $query = TenantActivityLogEntry::query()
         ->with(['causer'])
         ->where('tenant_id', $filter->tenantId)
         ->orderByDesc('created_at');

      if ($filter->event !== null && $filter->event !== '') {
         $query->where('event', $filter->event);
      }

      if ($filter->search !== '') {
         $search = mb_strtolower($filter->search);

         $query->where(function ($q) use ($search): void {
            $q->whereRaw('LOWER(description) LIKE ?', ['%' . $search . '%'])
               ->orWhereRaw("LOWER(COALESCE(event, '')) LIKE ?", ['%' . $search . '%'])
               ->orWhereRaw("LOWER(COALESCE(subject_type, '')) LIKE ?", ['%' . $search . '%']);
         });
      }

      return $query->paginate(
         perPage: $filter->perPage,
         page: $filter->page,
         pageName: $filter->pageName,
      );
   }
}
