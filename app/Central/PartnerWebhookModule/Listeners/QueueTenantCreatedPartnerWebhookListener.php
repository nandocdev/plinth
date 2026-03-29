<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Listeners;

use App\Central\PartnerWebhookModule\Actions\QueuePartnerWebhookDeliveriesAction;
use App\Central\PartnerWebhookModule\DTOs\QueuePartnerWebhookDeliveryData;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;

final class QueueTenantCreatedPartnerWebhookListener {
   public function __construct(
      private readonly QueuePartnerWebhookDeliveriesAction $queueAction,
   ) {
   }

   public function handle(TenantCreatedFromCentral $event): void {
      $tenant = $event->tenant;

      $this->queueAction->execute(new QueuePartnerWebhookDeliveryData(
         event: 'tenant.created',
         tenantId: (string) $tenant->id,
         payload: [
            'tenant_id' => (string) $tenant->id,
            'name' => $tenant->displayName(),
            'status' => $tenant->status(),
            'region' => $tenant->region(),
            'created_at' => optional($tenant->created_at)?->toISOString(),
         ],
      ));
   }
}
