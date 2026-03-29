<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Livewire\Forms;

use App\Central\PartnerWebhookModule\Enums\PartnerWebhookEvent;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PartnerWebhookEndpointForm extends Form {
   public string $name = '';

   public string $targetUrl = '';

   public string $signingSecret = '';

   /** @var list<string> */
   public array $subscribedEvents = [];

   public bool $isActive = true;

   /**
    * @return array{name: string, targetUrl: string, signingSecret: string, subscribedEvents: list<string>, isActive: bool}
    */
   public function payload(): array {
      $this->validate([
         'name' => ['required', 'string', 'min:3', 'max:120', 'unique:partner_webhook_endpoints,name'],
         'targetUrl' => ['required', 'url:http,https', 'max:2048'],
         'signingSecret' => ['required', 'string', 'min:16', 'max:255'],
         'subscribedEvents' => ['required', 'array', 'min:1'],
         'subscribedEvents.*' => ['required', 'string', Rule::in(PartnerWebhookEvent::values())],
         'isActive' => ['required', 'boolean'],
      ]);

      return [
         'name' => trim($this->name),
         'targetUrl' => trim($this->targetUrl),
         'signingSecret' => trim($this->signingSecret),
         'subscribedEvents' => array_values(array_unique($this->subscribedEvents)),
         'isActive' => $this->isActive,
      ];
   }

   public function clear(): void {
      $this->reset();
      $this->isActive = true;
   }
}
