<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Livewire\Forms;

use App\Tenant\WebhookModule\Enums\TenantWebhookEvent;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class WebhookEndpointForm extends Form {
   #[Validate('nullable|integer|min:1')]
   public ?int $endpointId = null;

   #[Validate('required|string|max:100')]
   public string $name = '';

   #[Validate('required|url|max:500')]
   public string $targetUrl = '';

   #[Validate('required|array|min:1')]
   public array $subscribedEvents = [];

   #[Validate('boolean')]
   public bool $isActive = true;

   #[Validate('integer|min:1|max:10')]
   public int $maxAttempts = 5;

   public function fillFromModel(\App\Tenant\WebhookModule\Models\WebhookEndpoint $endpoint): void {
      $this->endpointId = $endpoint->id;
      $this->name = $endpoint->name;
      $this->targetUrl = $endpoint->target_url;
      $this->subscribedEvents = is_array($endpoint->subscribed_events)
         ? $endpoint->subscribed_events
         : [];
      $this->isActive = $endpoint->is_active;
      $this->maxAttempts = $endpoint->max_attempts;
   }

   public function clear(): void {
      $this->reset();
   }

   /** @return array<string, mixed> */
   public function toArray(): array {
      $validEvents = TenantWebhookEvent::values();

      return [
         'endpoint_id' => $this->endpointId,
         'name' => $this->name,
         'target_url' => $this->targetUrl,
         'subscribed_events' => array_values(
            array_filter(
               $this->subscribedEvents,
               static fn(string $e): bool => in_array($e, $validEvents, true),
            )
         ),
         'is_active' => $this->isActive,
         'max_attempts' => $this->maxAttempts,
      ];
   }
}
