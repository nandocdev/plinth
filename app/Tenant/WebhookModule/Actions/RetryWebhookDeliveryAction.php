<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Actions;

use App\Tenant\WebhookModule\Jobs\DeliverTenantWebhookJob;
use App\Tenant\WebhookModule\Models\WebhookDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RetryWebhookDeliveryAction {
   public function execute(int $deliveryId): WebhookDelivery {
      /** @var WebhookDelivery $original */
      $original = WebhookDelivery::query()->with('endpoint')->findOrFail($deliveryId);

      $retry = DB::transaction(function () use ($original): WebhookDelivery {
         /** @var WebhookDelivery $retry */
         $retry = WebhookDelivery::query()->create([
            'tenant_webhook_endpoint_id' => $original->tenant_webhook_endpoint_id,
            'event' => $original->event,
            'delivery_uuid' => (string) Str::uuid(),
            'payload' => $original->payload,
            'status' => WebhookDelivery::STATUS_QUEUED,
            'attempts' => 0,
            'max_attempts' => $original->max_attempts,
         ]);

         return $retry;
      });

      DeliverTenantWebhookJob::dispatch($retry->id)->onQueue('webhooks');

      return $retry;
   }
}
