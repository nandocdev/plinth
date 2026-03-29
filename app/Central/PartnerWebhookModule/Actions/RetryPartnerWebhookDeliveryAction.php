<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Actions;

use App\Central\PartnerWebhookModule\Jobs\DispatchPartnerWebhookDeliveryJob;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use Illuminate\Support\Facades\DB;

final class RetryPartnerWebhookDeliveryAction {
   public function execute(int $deliveryId): PartnerWebhookDelivery {
      /** @var PartnerWebhookDelivery $delivery */
      $delivery = DB::connection('central')->transaction(function () use ($deliveryId): PartnerWebhookDelivery {
         /** @var PartnerWebhookDelivery $entity */
         $entity = PartnerWebhookDelivery::query()->findOrFail($deliveryId);

         $entity->update([
            'status' => PartnerWebhookDelivery::STATUS_QUEUED,
            'next_retry_at' => null,
            'last_error' => null,
            'response_status' => null,
            'response_body' => null,
         ]);

         return $entity->refresh();
      });

      DispatchPartnerWebhookDeliveryJob::dispatch($delivery->id)->onQueue('webhooks');

      return $delivery;
   }
}
