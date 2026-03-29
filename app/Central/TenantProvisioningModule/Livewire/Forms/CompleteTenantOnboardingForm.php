<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class CompleteTenantOnboardingForm extends Form {
   public string $name = '';

   public string $primaryDomain = '';

   public int $planId = 0;

   public string $billingPeriod = 'monthly';

   /**
    * @return array{name: string, primaryDomain: string, planId: int, billingPeriod: string}
    */
   public function payload(): array {
      $this->validate([
         'name' => ['required', 'string', 'min:3', 'max:100'],
         'primaryDomain' => ['required', 'string', 'max:255', 'unique:domains,domain', 'regex:/^[a-z0-9][a-z0-9\-.]+[a-z0-9]$/'],
         'planId' => ['required', 'integer', 'exists:plans,id'],
         'billingPeriod' => ['required', 'string', Rule::in(['monthly', 'yearly'])],
      ]);

      return [
         'name' => trim($this->name),
         'primaryDomain' => trim(strtolower($this->primaryDomain)),
         'planId' => $this->planId,
         'billingPeriod' => $this->billingPeriod,
      ];
   }

   public function clear(): void {
      $this->reset();
      $this->billingPeriod = 'monthly';
   }
}
