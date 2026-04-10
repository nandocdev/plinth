<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\FeatureFlagsModule\DTOs;

final readonly class PlanFeaturesData {
   /**
    * @param list<string> $features   Feature flags booleanos habilitados en el plan (e.g. 'api_access', 'advanced_reports')
    */
   public function __construct(
      public ?string $planName,
      public ?string $planSlug,
      public ?string $subscriptionStatus,
      /** @var list<string> */
      public array $features,
      public ?int $maxUsersSoft,
      public ?int $maxUsersHard,
      public ?int $maxStorageMbSoft,
      public ?int $maxStorageMbHard,
   ) {
   }

   public function hasPlan(): bool {
      return $this->planSlug !== null;
   }

   public function hasFeature(string $flag): bool {
      return in_array($flag, $this->features, true);
   }

   public function isActive(): bool {
      return in_array($this->subscriptionStatus, ['active', 'trialing'], true);
   }
}
