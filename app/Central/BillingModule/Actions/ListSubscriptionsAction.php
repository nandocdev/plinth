<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListSubscriptionsAction {
   public function execute(string $search = '', int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator {
      $query = TenantSubscription::query()
         ->with([
            'plan:id,name,slug',
            'tenant:id,data',
         ])
         ->latest('created_at');

      if ($search !== '') {
         $query->where(function ($builder) use ($search): void {
            $builder
               ->where('tenant_id', 'like', "%{$search}%")
               ->orWhere('status', 'like', "%{$search}%")
               ->orWhereHas('plan', function ($planQuery) use ($search): void {
                  $planQuery->where('name', 'like', "%{$search}%");
               });
         });
      }

      return $query->paginate(
         $perPage,
         ['id', 'tenant_id', 'plan_id', 'billing_period', 'status', 'trial_ends_at', 'starts_at', 'ends_at', 'price_snapshot_cents'],
         $pageName,
      );
   }
}
