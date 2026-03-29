<?php

declare(strict_types=1);

namespace App\Central\BillingModule\DTOs;

final readonly class DlocalWebhookData {
   /**
    * @param array<string, mixed> $payload
    */
   public function __construct(
      public string $eventId,
      public ?string $externalSubscriptionId,
      public ?string $status,
      public string $payloadHash,
      public array $payload,
   ) {
   }
}
