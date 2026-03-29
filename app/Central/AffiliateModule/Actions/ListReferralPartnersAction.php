<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Actions;

use App\Central\AffiliateModule\Models\ReferralPartner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListReferralPartnersAction {
   public function execute(string $search, int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator {
      return ReferralPartner::query()
         ->withCount('conversions')
         ->when($search !== '', function ($query) use ($search): void {
            $term = '%' . mb_strtolower($search) . '%';
            $query->whereRaw('LOWER(code) LIKE ?', [$term])
               ->orWhereRaw('LOWER(name) LIKE ?', [$term])
               ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
         })
         ->orderByDesc('created_at')
         ->paginate($perPage, ['*'], $pageName);
   }
}
