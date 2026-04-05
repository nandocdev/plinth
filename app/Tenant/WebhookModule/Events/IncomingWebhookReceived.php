<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class IncomingWebhookReceived {
   use Dispatchable;
   use SerializesModels;

   public function __construct(
      public readonly int $logId,
      public readonly int $tokenId,
      /** @var array<string, mixed> */
      public readonly array $payload,
   ) {
   }
}
