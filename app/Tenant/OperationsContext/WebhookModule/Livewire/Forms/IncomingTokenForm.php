<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class IncomingTokenForm extends Form {
   #[Validate('required|string|max:100')]
   public string $name = '';

   public function clear(): void {
      $this->reset();
   }

   /** @return array<string, mixed> */
   public function toArray(): array {
      return ['name' => $this->name];
   }
}
