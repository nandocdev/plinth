<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\Plan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPlansAction {
   public function execute(string $search = '', int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator {
      $query = Plan::query()
         ->withCount('subscriptions')
         ->latest('created_at');

      if ($search !== '') {
         $query->where(function ($builder) use ($search): void {
            $builder
               ->where('name', 'like', "%{$search}%")
               ->orWhere('slug', 'like', "%{$search}%");
         });
      }

      return $query->paginate(
         $perPage,
         ['id', 'name', 'slug', 'price_monthly_cents', 'price_yearly_cents', 'trial_days', 'features', 'is_active', 'sort_order'],
         $pageName,
      );
   }
}
