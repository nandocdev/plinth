<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Actions;

use App\Tenant\WebhookModule\Models\WebhookEndpoint;
use Illuminate\Support\Facades\DB;

final class DeleteWebhookEndpointAction {
   public function execute(int $endpointId): void {
      /** @var WebhookEndpoint $endpoint */
      $endpoint = WebhookEndpoint::query()->findOrFail($endpointId);

      DB::transaction(static function () use ($endpoint): void {
         // Deliveries se borran en cascada por FK
         $endpoint->delete();
      });
   }
}
