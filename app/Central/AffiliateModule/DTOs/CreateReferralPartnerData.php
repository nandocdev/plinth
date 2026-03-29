<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\DTOs;

final readonly class CreateReferralPartnerData {
   public function __construct(
      public string $code,
      public string $name,
      public string $email,
      public string $payoutType,
      public float $payoutValue,
      public bool $isActive,
      public ?string $notes,
   ) {
   }
}
