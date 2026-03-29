<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\DTOs;

final readonly class CreatePartnerWebhookEndpointData {
   /**
    * @param list<string> $subscribedEvents
    */
   public function __construct(
      public string $name,
      public string $targetUrl,
      public string $signingSecret,
      public array $subscribedEvents,
      public bool $isActive,
   ) {
   }
}
