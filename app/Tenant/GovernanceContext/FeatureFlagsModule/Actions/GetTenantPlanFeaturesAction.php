<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\FeatureFlagsModule\Actions;

use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Tenant\GovernanceContext\FeatureFlagsModule\DTOs\PlanFeaturesData;

final class GetTenantPlanFeaturesAction {
   public function execute(string $tenantId): PlanFeaturesData {
      // Lectura explícita en BD central — el tenant context puede estar activo
      // pero la información de planes y suscripciones es siempre central.
      $subscription = TenantSubscription::on('central')
         ->with('plan')
         ->where('tenant_id', $tenantId)
         ->whereIn('status', [
            TenantSubscription::STATUS_TRIALING,
            TenantSubscription::STATUS_ACTIVE,
            TenantSubscription::STATUS_PAST_DUE,
         ])
         ->orderByDesc('id')
         ->first();

      if (! $subscription instanceof TenantSubscription) {
         return new PlanFeaturesData(
            planName: null,
            planSlug: null,
            subscriptionStatus: null,
            features: [],
            maxUsersSoft: null,
            maxUsersHard: null,
            maxStorageMbSoft: null,
            maxStorageMbHard: null,
         );
      }

      /** @var Plan|null $plan */
      $plan = $subscription->plan;

      /** @var list<string> $features */
      $features = is_array($plan?->features) ? array_values(array_filter($plan->features, 'is_string')) : [];

      return new PlanFeaturesData(
         planName: $plan?->name,
         planSlug: $plan?->slug,
         subscriptionStatus: $subscription->status,
         features: $features,
         maxUsersSoft: $plan?->max_users_soft,
         maxUsersHard: $plan?->max_users_hard,
         maxStorageMbSoft: $plan?->max_storage_mb_soft,
         maxStorageMbHard: $plan?->max_storage_mb_hard,
      );
   }
}
