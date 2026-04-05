<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OutgoingWebhookDispatched {
   use Dispatchable;
   use SerializesModels;

   public function __construct(
      public readonly int $deliveryId,
      public readonly string $event,
      public readonly string $status,
   ) {
   }
}
