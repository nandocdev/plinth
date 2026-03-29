<?php

declare(strict_types=1);

namespace App\Central\BillingModule\DTOs;

final readonly class CreatePlanData {
   /**
    * @param array<int, string> $features
    */
   public function __construct(
      public string $name,
      public string $slug,
      public int $priceMonthlyCents,
      public ?int $priceYearlyCents,
      public int $trialDays,
      public array $features,
      public bool $isActive,
      public int $sortOrder,
   ) {
   }
}
