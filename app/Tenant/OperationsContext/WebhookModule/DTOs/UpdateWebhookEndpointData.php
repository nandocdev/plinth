<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\DTOs;

final readonly class UpdateWebhookEndpointData {
   public function __construct(
      public int $endpointId,
      public string $name,
      public string $targetUrl,
      /** @var list<string> */
      public array $subscribedEvents,
      public bool $isActive,
      public int $maxAttempts,
   ) {
   }

   /** @param array<string, mixed> $data */
   public static function fromArray(array $data): self {
      return new self(
         endpointId: (int) ($data['endpoint_id'] ?? 0),
         name: (string) ($data['name'] ?? ''),
         targetUrl: (string) ($data['target_url'] ?? ''),
         subscribedEvents: array_values((array) ($data['subscribed_events'] ?? [])),
         isActive: (bool) ($data['is_active'] ?? true),
         maxAttempts: (int) ($data['max_attempts'] ?? 5),
      );
   }
}
