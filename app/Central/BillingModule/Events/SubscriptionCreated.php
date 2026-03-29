<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Events;

use App\Central\BillingModule\Models\TenantSubscription;

final readonly class SubscriptionCreated {
   public function __construct(
      public TenantSubscription $subscription,
   ) {
   }
}
