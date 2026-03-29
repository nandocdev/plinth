<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Actions;

use App\Central\PartnerWebhookModule\DTOs\QueuePartnerWebhookDeliveryData;
use App\Central\PartnerWebhookModule\Jobs\DispatchPartnerWebhookDeliveryJob;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class QueuePartnerWebhookDeliveriesAction {
   /**
    * @return list<int>
    */
   public function execute(QueuePartnerWebhookDeliveryData $data): array {
      $deliveryIds = DB::connection('central')->transaction(function () use ($data): array {
         $endpoints = PartnerWebhookEndpoint::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn(PartnerWebhookEndpoint $endpoint): bool => in_array($data->event, (array) $endpoint->subscribed_events, true))
            ->values();

         $ids = [];

         foreach ($endpoints as $endpoint) {
            /** @var PartnerWebhookDelivery $delivery */
            $delivery = PartnerWebhookDelivery::query()->create([
               'partner_webhook_endpoint_id' => $endpoint->id,
               'event' => $data->event,
               'tenant_id' => $data->tenantId,
               'delivery_uuid' => (string) Str::uuid(),
               'payload' => $data->payload,
               'status' => PartnerWebhookDelivery::STATUS_QUEUED,
               'attempts' => 0,
               'max_attempts' => 5,
            ]);

            $ids[] = $delivery->id;
         }

         return $ids;
      });

      foreach ($deliveryIds as $deliveryId) {
         DispatchPartnerWebhookDeliveryJob::dispatch($deliveryId)->onQueue('webhooks');
      }

      return $deliveryIds;
   }
}
