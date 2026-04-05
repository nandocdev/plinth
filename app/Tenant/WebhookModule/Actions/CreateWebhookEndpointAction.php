<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Actions;

use App\Tenant\WebhookModule\DTOs\CreateWebhookEndpointData;
use App\Tenant\WebhookModule\Models\WebhookEndpoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateWebhookEndpointAction {
   public function execute(CreateWebhookEndpointData $data): WebhookEndpoint {
      return DB::transaction(function () use ($data): WebhookEndpoint {
         /** @var WebhookEndpoint $endpoint */
         $endpoint = WebhookEndpoint::query()->create([
            'name' => $data->name,
            'target_url' => $data->targetUrl,
            'signing_secret' => $data->signingSecret !== '' ? $data->signingSecret : Str::random(32),
            'subscribed_events' => $data->subscribedEvents,
            'is_active' => $data->isActive,
            'max_attempts' => $data->maxAttempts,
         ]);

         return $endpoint;
      });
   }
}
