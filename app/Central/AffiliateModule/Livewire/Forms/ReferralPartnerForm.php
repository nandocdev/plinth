<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class ReferralPartnerForm extends Form {
   public string $code = '';

   public string $name = '';

   public string $email = '';

   public string $payoutType = 'percentage';

   public string $payoutValue = '10';

   public bool $isActive = true;

   public string $notes = '';

   /**
    * @return array{code: string, name: string, email: string, payoutType: string, payoutValue: float, isActive: bool, notes: ?string}
    */
   public function payload(): array {
      $this->validate([
         'code' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:referral_partners,code'],
         'name' => ['required', 'string', 'min:3', 'max:120'],
         'email' => ['required', 'email', 'max:190', 'unique:referral_partners,email'],
         'payoutType' => ['required', 'string', Rule::in(['fixed', 'percentage'])],
         'payoutValue' => ['required', 'numeric', 'min:0'],
         'isActive' => ['required', 'boolean'],
         'notes' => ['nullable', 'string', 'max:1000'],
      ]);

      return [
         'code' => strtoupper(trim($this->code)),
         'name' => trim($this->name),
         'email' => strtolower(trim($this->email)),
         'payoutType' => $this->payoutType,
         'payoutValue' => (float) $this->payoutValue,
         'isActive' => $this->isActive,
         'notes' => trim($this->notes) !== '' ? trim($this->notes) : null,
      ];
   }

   public function clear(): void {
      $this->reset();
      $this->payoutType = 'percentage';
      $this->payoutValue = '10';
      $this->isActive = true;
   }
}
