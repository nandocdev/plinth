<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class CreateDomainForm extends Form {
   #[Validate('required|string|exists:tenants,id')]
   public string $tenantId = '';

   #[Validate('required|string|max:255|unique:domains,domain|regex:/^[a-z0-9][a-z0-9\-.]+[a-z0-9]$/')]
   public string $domain = '';

   /**
    * @return array{tenantId: string, domain: string}
    */
   public function payload(): array {
      $this->validate();

      return [
         'tenantId' => trim($this->tenantId),
         'domain' => trim(strtolower($this->domain)),
      ];
   }

   public function clear(): void {
      $this->reset();
   }
}
