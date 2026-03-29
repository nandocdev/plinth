<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\DTOs;

final readonly class BillingOverviewData {
   public function __construct(
      public ?string $planName,
      public ?string $planSlug,
      public ?int $priceSnapshotCents,
      public ?string $billingPeriod,
      public ?string $status,
      public ?string $trialEndsAt,
      public ?string $endsAt,
      public ?string $startsAt,
      public ?int $currentPlanId,
   ) {
   }

   public function hasSubscription(): bool {
      return $this->status !== null;
   }

   public function isActive(): bool {
      return in_array($this->status, ['active', 'trialing'], true);
   }
}
