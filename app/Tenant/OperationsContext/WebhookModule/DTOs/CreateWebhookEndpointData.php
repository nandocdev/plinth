<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\DTOs;

final readonly class CreateWebhookEndpointData {
   public function __construct(
      public string $name,
      public string $targetUrl,
      public string $signingSecret,
      /** @var list<string> */
      public array $subscribedEvents,
      public bool $isActive,
      public int $maxAttempts,
   ) {
   }

   /** @param array<string, mixed> $data */
   public static function fromArray(array $data): self {
      return new self(
         name: (string) ($data['name'] ?? ''),
         targetUrl: (string) ($data['target_url'] ?? ''),
         signingSecret: (string) ($data['signing_secret'] ?? ''),
         subscribedEvents: array_values((array) ($data['subscribed_events'] ?? [])),
         isActive: (bool) ($data['is_active'] ?? true),
         maxAttempts: (int) ($data['max_attempts'] ?? 5),
      );
   }
}
