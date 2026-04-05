<?php

declare(strict_types=1);

namespace App\Tenant\CustomDomainModule\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class CustomDomainForm extends Form {
   #[Validate(['required', 'string', 'max:255', 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/'])]
   public string $domain = '';

   public function clear(): void {
      $this->domain = '';
   }
}
