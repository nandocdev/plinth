<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class CreateTenantForm extends Form {
   #[Validate('required|string|min:3|max:100')]
   public string $name = '';

   #[Validate('required|string|max:255|unique:domains,domain|regex:/^[a-z0-9][a-z0-9\-.]+[a-z0-9]$/')]
   public string $primaryDomain = '';

   /**
    * @return array{name: string, primaryDomain: string}
    */
   public function payload(): array {
      $this->validate();

      return [
         'name' => trim($this->name),
         'primaryDomain' => trim(strtolower($this->primaryDomain)),
      ];
   }

   public function clear(): void {
      $this->reset();
   }
}
