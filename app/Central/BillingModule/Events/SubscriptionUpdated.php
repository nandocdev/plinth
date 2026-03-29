<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Events;

use App\Central\BillingModule\Models\TenantSubscription;

final readonly class SubscriptionUpdated {
   public function __construct(
      public TenantSubscription $subscription,
      public string $previousStatus,
   ) {
   }
}
