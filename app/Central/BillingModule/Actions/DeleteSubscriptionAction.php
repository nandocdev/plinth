<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;

final class DeleteSubscriptionAction {
   public function execute(int $subscriptionId): void {
      DB::connection('central')->transaction(function () use ($subscriptionId): void {
         /** @var TenantSubscription $subscription */
         $subscription = TenantSubscription::query()->findOrFail($subscriptionId);
         $subscription->delete();
      });
   }
}
