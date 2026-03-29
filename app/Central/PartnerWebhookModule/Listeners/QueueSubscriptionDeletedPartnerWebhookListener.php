<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Listeners;

use App\Central\BillingModule\Events\SubscriptionDeleted;
use App\Central\PartnerWebhookModule\Actions\QueuePartnerWebhookDeliveriesAction;
use App\Central\PartnerWebhookModule\DTOs\QueuePartnerWebhookDeliveryData;

final class QueueSubscriptionDeletedPartnerWebhookListener {
   public function __construct(
      private readonly QueuePartnerWebhookDeliveriesAction $queueAction,
   ) {
   }

   public function handle(SubscriptionDeleted $event): void {
      $subscription = $event->subscription;

      $this->queueAction->execute(new QueuePartnerWebhookDeliveryData(
         event: 'subscription.deleted',
         tenantId: (string) $subscription->tenant_id,
         payload: [
            'subscription_id' => $subscription->id,
            'tenant_id' => (string) $subscription->tenant_id,
            'plan_id' => $subscription->plan_id,
            'status' => $subscription->status,
            'deleted_at' => now()->toISOString(),
         ],
      ));
   }
}
