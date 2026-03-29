<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Actions;

use App\Central\ActivityLogModule\DTOs\ListGlobalLogsFilterData;
use App\Central\ActivityLogModule\Models\ActivityLogEntry;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListGlobalLogsAction {
   public function execute(ListGlobalLogsFilterData $filter): LengthAwarePaginator {
      $query = ActivityLogEntry::query()
         ->with(['causer'])
         ->orderByDesc('created_at');

      if ($filter->tenantId !== null && $filter->tenantId !== '') {
         $query->where('properties->tenant_id', $filter->tenantId);
      }

      if ($filter->event !== null && $filter->event !== '') {
         $query->where('event', $filter->event);
      }

      if ($filter->causerId !== null) {
         $query->where('causer_type', 'App\\Central\\AuthenticationModule\\Models\\User')
            ->where('causer_id', $filter->causerId);
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
