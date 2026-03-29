<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\Models\TenantSubscription;

final class FindSubscriptionAction {
   public function execute(int $subscriptionId): TenantSubscription {
      /** @var TenantSubscription $subscription */
      $subscription = TenantSubscription::query()->findOrFail($subscriptionId);

      return $subscription;
   }
}
