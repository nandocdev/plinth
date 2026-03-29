<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\TenantSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SyncSubscriptionLifecycleAction {
   public function execute(): int {
      $now = CarbonImmutable::now();

      return DB::connection('central')->transaction(function () use ($now): int {
         /** @var Collection<int, TenantSubscription> $subscriptions */
         $subscriptions = TenantSubscription::query()
            ->where('status', TenantSubscription::STATUS_TRIALING)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', $now->toDateTimeString())
            ->lockForUpdate()
            ->get();

         foreach ($subscriptions as $subscription) {
            TenantSubscription::assertValidTransition($subscription->status, TenantSubscription::STATUS_ACTIVE);

            $subscription->update([
               'status' => TenantSubscription::STATUS_ACTIVE,
               'trial_ends_at' => null,
               'ends_at' => null,
            ]);
         }

         return $subscriptions->count();
      });
   }
}
