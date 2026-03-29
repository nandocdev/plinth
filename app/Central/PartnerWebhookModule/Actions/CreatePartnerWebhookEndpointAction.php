<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Actions;

use App\Central\PartnerWebhookModule\DTOs\CreatePartnerWebhookEndpointData;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use Illuminate\Support\Facades\DB;

final class CreatePartnerWebhookEndpointAction {
   public function execute(CreatePartnerWebhookEndpointData $data): PartnerWebhookEndpoint {
      /** @var PartnerWebhookEndpoint $endpoint */
      $endpoint = DB::connection('central')->transaction(function () use ($data): PartnerWebhookEndpoint {
         return PartnerWebhookEndpoint::query()->create([
            'name' => $data->name,
            'target_url' => $data->targetUrl,
            'signing_secret' => $data->signingSecret,
            'subscribed_events' => $data->subscribedEvents,
            'is_active' => $data->isActive,
         ]);
      });

      return $endpoint;
   }
}
