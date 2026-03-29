<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Listeners;

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Central\PartnerWebhookModule\Actions\QueuePartnerWebhookDeliveriesAction;
use App\Central\PartnerWebhookModule\DTOs\QueuePartnerWebhookDeliveryData;

final class QueueSubscriptionCreatedPartnerWebhookListener {
   public function __construct(
      private readonly QueuePartnerWebhookDeliveriesAction $queueAction,
   ) {
   }

   public function handle(SubscriptionCreated $event): void {
      $subscription = $event->subscription;

      $this->queueAction->execute(new QueuePartnerWebhookDeliveryData(
         event: 'subscription.created',
         tenantId: (string) $subscription->tenant_id,
         payload: [
            'subscription_id' => $subscription->id,
            'tenant_id' => (string) $subscription->tenant_id,
            'plan_id' => $subscription->plan_id,
            'status' => $subscription->status,
            'billing_period' => $subscription->billing_period,
            'created_at' => optional($subscription->created_at)?->toISOString(),
         ],
      ));
   }
}
