<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Actions;

use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPartnerWebhookDeliveriesAction {
   public function execute(string $search = '', int $perPage = 10, string $pageName = 'deliveriesPage'): LengthAwarePaginator {
      return PartnerWebhookDelivery::query()
         ->with(['endpoint'])
         ->when(trim($search) !== '', function ($q) use ($search): void {
            $needle = mb_strtolower(trim($search));
            $q->whereRaw('LOWER(event) LIKE ?', ['%' . $needle . '%'])
               ->orWhereRaw('LOWER(status) LIKE ?', ['%' . $needle . '%'])
               ->orWhereRaw("LOWER(COALESCE(tenant_id, '')) LIKE ?", ['%' . $needle . '%'])
               ->orWhereRaw('LOWER(delivery_uuid) LIKE ?', ['%' . $needle . '%']);
         })
         ->orderByDesc('id')
         ->paginate($perPage, pageName: $pageName);
   }
}
