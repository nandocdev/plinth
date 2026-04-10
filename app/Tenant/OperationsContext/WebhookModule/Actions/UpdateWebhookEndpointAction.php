<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Actions;

use App\Tenant\OperationsContext\WebhookModule\DTOs\UpdateWebhookEndpointData;
use App\Tenant\OperationsContext\WebhookModule\Models\WebhookEndpoint;
use Illuminate\Support\Facades\DB;

final class UpdateWebhookEndpointAction {
   public function execute(UpdateWebhookEndpointData $data): WebhookEndpoint {
      /** @var WebhookEndpoint $endpoint */
      $endpoint = WebhookEndpoint::query()->findOrFail($data->endpointId);

      return DB::transaction(function () use ($endpoint, $data): WebhookEndpoint {
         $endpoint->update([
            'name' => $data->name,
            'target_url' => $data->targetUrl,
            'subscribed_events' => $data->subscribedEvents,
            'is_active' => $data->isActive,
            'max_attempts' => $data->maxAttempts,
         ]);

         return $endpoint->fresh() ?? $endpoint;
      });
   }
}
