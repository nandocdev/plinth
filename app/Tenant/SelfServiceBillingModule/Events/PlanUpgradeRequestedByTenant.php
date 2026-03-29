<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Events;

use App\Central\BillingModule\Models\TenantSubscription;

final class PlanUpgradeRequestedByTenant {
   public function __construct(
      public readonly TenantSubscription $subscription,
      public readonly int $previousPlanId,
   ) {
   }
}
