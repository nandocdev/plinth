<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Actions;

use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use Illuminate\Support\Facades\DB;

final class TogglePartnerWebhookEndpointStatusAction {
   public function execute(int $endpointId, bool $isActive): PartnerWebhookEndpoint {
      /** @var PartnerWebhookEndpoint $endpoint */
      $endpoint = DB::connection('central')->transaction(function () use ($endpointId, $isActive): PartnerWebhookEndpoint {
         /** @var PartnerWebhookEndpoint $entity */
         $entity = PartnerWebhookEndpoint::query()->findOrFail($endpointId);
         $entity->update(['is_active' => $isActive]);

         return $entity->refresh();
      });

      return $endpoint;
   }
}
