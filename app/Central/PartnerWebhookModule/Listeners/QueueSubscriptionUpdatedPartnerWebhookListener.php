<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Listeners;

use App\Central\BillingModule\Events\SubscriptionUpdated;
use App\Central\PartnerWebhookModule\Actions\QueuePartnerWebhookDeliveriesAction;
use App\Central\PartnerWebhookModule\DTOs\QueuePartnerWebhookDeliveryData;

final class QueueSubscriptionUpdatedPartnerWebhookListener {
   public function __construct(
      private readonly QueuePartnerWebhookDeliveriesAction $queueAction,
   ) {
   }

   public function handle(SubscriptionUpdated $event): void {
      $subscription = $event->subscription;

      $this->queueAction->execute(new QueuePartnerWebhookDeliveryData(
         event: 'subscription.updated',
         tenantId: (string) $subscription->tenant_id,
         payload: [
            'subscription_id' => $subscription->id,
            'tenant_id' => (string) $subscription->tenant_id,
            'plan_id' => $subscription->plan_id,
            'status' => $subscription->status,
            'previous_status' => $event->previousStatus,
            'billing_period' => $subscription->billing_period,
            'updated_at' => optional($subscription->updated_at)?->toISOString(),
         ],
      ));
   }
}
