<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Listeners;

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantInvoice;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Tenant\SelfServiceBillingModule\Actions\RecordTenantInvoiceAction;

final class CreateInvoiceOnSubscriptionCreatedListener {
   public function __construct(
      private readonly RecordTenantInvoiceAction $recordInvoice,
   ) {
   }

   public function handle(SubscriptionCreated $event): void {
      $subscription = $event->subscription;

      // Solo genera factura si la suscripción inicia en estado activo (pago inmediato).
      if ($subscription->status !== TenantSubscription::STATUS_ACTIVE) {
         return;
      }

      /** @var Plan|null $plan */
      $plan = $subscription->relationLoaded('plan')
         ? $subscription->plan
         : Plan::on('central')->find($subscription->plan_id);

      $this->recordInvoice->execute(
         tenantId: (string) $subscription->tenant_id,
         subscriptionId: (int) $subscription->id,
         amountCents: (int) $subscription->price_snapshot_cents,
         billingPeriod: (string) $subscription->billing_period,
         description: sprintf(
            'Activación de suscripción — Plan %s (%s)',
            $plan?->name ?? 'N/A',
            $subscription->billing_period,
         ),
         status: TenantInvoice::STATUS_PAID,
      );
   }
}
