<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Actions;

use App\Central\AffiliateModule\Models\ReferralConversion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListReferralConversionsAction {
   public function execute(string $search, int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator {
      return ReferralConversion::query()
         ->with('partner')
         ->when($search !== '', function ($query) use ($search): void {
            $term = '%' . mb_strtolower($search) . '%';

            $query->whereRaw('LOWER(tenant_id) LIKE ?', [$term])
               ->orWhereRaw('LOWER(status) LIKE ?', [$term])
               ->orWhereRaw("LOWER(COALESCE(referred_email, '')) LIKE ?", [$term]);
         })
         ->orderByDesc('created_at')
         ->paginate($perPage, ['*'], $pageName);
   }
}
