<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class CompleteTenantOnboardingData {
   public function __construct(
      public string $name,
      public string $primaryDomain,
      public int $planId,
      public string $billingPeriod,
      public ?string $region = null,
   ) {
   }
}
