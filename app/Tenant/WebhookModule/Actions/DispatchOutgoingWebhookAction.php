<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Actions;

use App\Tenant\WebhookModule\Enums\TenantWebhookEvent;
use App\Tenant\WebhookModule\Jobs\DeliverTenantWebhookJob;
use App\Tenant\WebhookModule\Models\WebhookDelivery;
use App\Tenant\WebhookModule\Models\WebhookEndpoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DispatchOutgoingWebhookAction {
   /**
    * Busca todos los endpoints suscritos al evento y encola la entrega.
    *
    * @param array<string, mixed> $payload
    */
   public function execute(TenantWebhookEvent $event, array $payload): void {
      $endpoints = WebhookEndpoint::query()
         ->where('is_active', true)
         ->get();

      foreach ($endpoints as $endpoint) {
         $subscribed = is_array($endpoint->subscribed_events)
            ? $endpoint->subscribed_events
            : [];

         if (! in_array($event->value, $subscribed, true)) {
            continue;
         }

         $delivery = DB::transaction(function () use ($endpoint, $event, $payload): WebhookDelivery {
            /** @var WebhookDelivery $delivery */
            $delivery = WebhookDelivery::query()->create([
               'tenant_webhook_endpoint_id' => $endpoint->id,
               'event' => $event->value,
               'delivery_uuid' => (string) Str::uuid(),
               'payload' => $payload,
               'status' => WebhookDelivery::STATUS_QUEUED,
               'attempts' => 0,
               'max_attempts' => $endpoint->max_attempts,
            ]);

            return $delivery;
         });

         DeliverTenantWebhookJob::dispatch($delivery->id)->onQueue('webhooks');
      }
   }
}
