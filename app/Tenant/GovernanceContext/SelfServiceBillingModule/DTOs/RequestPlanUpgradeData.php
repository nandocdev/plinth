<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\DTOs;

final readonly class RequestPlanUpgradeData {
   public function __construct(
      public string $tenantId,
      public int $planId,
      public string $billingPeriod,
      public string $methodType = 'card',
   ) {
   }
}
