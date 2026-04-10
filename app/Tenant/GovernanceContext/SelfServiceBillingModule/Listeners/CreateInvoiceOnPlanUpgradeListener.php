<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\Listeners;

use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantInvoice;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\RecordTenantInvoiceAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Events\PlanUpgradeRequestedByTenant;

final class CreateInvoiceOnPlanUpgradeListener {
   public function __construct(
      private readonly RecordTenantInvoiceAction $recordInvoice,
   ) {
   }

   public function handle(PlanUpgradeRequestedByTenant $event): void {
      $subscription = $event->subscription;

      if (! in_array($subscription->status, [TenantSubscription::STATUS_ACTIVE, TenantSubscription::STATUS_TRIALING], true)) {
         return;
      }

      /** @var Plan|null $newPlan */
      $newPlan = $subscription->relationLoaded('plan')
         ? $subscription->plan
         : Plan::on('central')->find($subscription->plan_id);

      /** @var Plan|null $previousPlan */
      $previousPlan = Plan::on('central')->find($event->previousPlanId);

      $this->recordInvoice->execute(
         tenantId: (string) $subscription->tenant_id,
         subscriptionId: (int) $subscription->id,
         amountCents: (int) $subscription->price_snapshot_cents,
         billingPeriod: (string) $subscription->billing_period,
         description: sprintf(
            'Cambio de plan: %s → %s (%s)',
            $previousPlan?->name ?? 'N/A',
            $newPlan?->name ?? 'N/A',
            $subscription->billing_period,
         ),
         status: TenantInvoice::STATUS_PAID,
      );
   }
}
