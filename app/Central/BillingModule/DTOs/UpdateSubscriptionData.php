<?php

declare(strict_types=1);

namespace App\Central\BillingModule\DTOs;

final readonly class UpdateSubscriptionData {
   public function __construct(
      public int $subscriptionId,
      public string $tenantId,
      public int $planId,
      public string $billingPeriod,
      public string $status,
      public ?string $trialEndsAt,
      public ?string $endsAt,
   ) {
   }
}
