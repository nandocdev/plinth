<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\DTOs;

final readonly class QueuePartnerWebhookDeliveryData {
   /**
    * @param array<string, mixed> $payload
    */
   public function __construct(
      public string $event,
      public ?string $tenantId,
      public array $payload,
   ) {
   }
}
