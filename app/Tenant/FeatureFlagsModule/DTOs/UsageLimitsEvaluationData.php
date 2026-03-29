<?php

declare(strict_types=1);

namespace App\Tenant\FeatureFlagsModule\DTOs;

final readonly class UsageLimitsEvaluationData {
   /**
    * @param list<string> $softWarnings
    * @param list<string> $hardViolations
    */
   public function __construct(
      public bool $softLimitReached,
      public bool $hardLimitReached,
      public array $softWarnings,
      public array $hardViolations,
   ) {
   }
}
