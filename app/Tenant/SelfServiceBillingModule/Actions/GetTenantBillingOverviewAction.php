<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Actions;

use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Tenant\SelfServiceBillingModule\DTOs\BillingOverviewData;

final class GetTenantBillingOverviewAction {
   public function execute(string $tenantId): BillingOverviewData {
      // Consulta explícita en BD central — el tenant context está activo pero la info de billing es central.
      $subscription = TenantSubscription::on('central')
         ->with('plan')
         ->where('tenant_id', $tenantId)
         ->first();

      if (! $subscription instanceof TenantSubscription) {
         return new BillingOverviewData(
            planName: null,
            planSlug: null,
            priceSnapshotCents: null,
            billingPeriod: null,
            status: null,
            trialEndsAt: null,
            endsAt: null,
            startsAt: null,
            currentPlanId: null,
         );
      }

      /** @var Plan|null $plan */
      $plan = $subscription->plan;

      return new BillingOverviewData(
         planName: $plan?->name,
         planSlug: $plan?->slug,
         priceSnapshotCents: $subscription->price_snapshot_cents,
         billingPeriod: $subscription->billing_period,
         status: $subscription->status,
         trialEndsAt: $subscription->trial_ends_at?->toDateTimeString(),
         endsAt: $subscription->ends_at?->toDateTimeString(),
         startsAt: $subscription->starts_at?->toDateTimeString(),
         currentPlanId: $plan?->id,
      );
   }
}
