<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Actions;

use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPartnerWebhookEndpointsAction {
   public function execute(string $search = '', int $perPage = 10, string $pageName = 'endpointsPage'): LengthAwarePaginator {
      return PartnerWebhookEndpoint::query()
         ->withCount('deliveries')
         ->when(trim($search) !== '', function ($q) use ($search): void {
            $needle = mb_strtolower(trim($search));
            $q->whereRaw('LOWER(name) LIKE ?', ['%' . $needle . '%'])
               ->orWhereRaw('LOWER(target_url) LIKE ?', ['%' . $needle . '%']);
         })
         ->orderByDesc('id')
         ->paginate($perPage, pageName: $pageName);
   }
}
