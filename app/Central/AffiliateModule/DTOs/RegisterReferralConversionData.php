<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\DTOs;

final readonly class RegisterReferralConversionData {
   /**
    * @param array<string, mixed> $metadata
    */
   public function __construct(
      public string $partnerCode,
      public string $tenantId,
      public ?string $referredEmail,
      public array $metadata = [],
   ) {
   }
}
