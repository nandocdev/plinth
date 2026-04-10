<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\FeatureFlagsModule\Actions;

final class CheckTenantHasFeatureAction {
   public function __construct(
      private readonly GetTenantPlanFeaturesAction $getPlanFeatures,
   ) {
   }

   public function execute(string $tenantId, string $flag): bool {
      return $this->getPlanFeatures->execute($tenantId)->hasFeature($flag);
   }
}
