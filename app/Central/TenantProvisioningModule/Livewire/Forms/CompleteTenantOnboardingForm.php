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

   public string $region = '';

   /**
    * @return array{name: string, primaryDomain: string, planId: int, billingPeriod: string, region: string}
    */
   public function payload(): array {
      $this->validate([
         'name' => ['required', 'string', 'min:3', 'max:100'],
         'primaryDomain' => ['required', 'string', 'max:255', 'unique:domains,domain', 'regex:/^[a-z0-9][a-z0-9\-.]+[a-z0-9]$/'],
         'planId' => ['required', 'integer', 'exists:plans,id'],
         'billingPeriod' => ['required', 'string', Rule::in(['monthly', 'yearly'])],
         'region' => ['required', 'string', Rule::in($this->availableRegions())],
      ]);

      return [
         'name' => trim($this->name),
         'primaryDomain' => trim(strtolower($this->primaryDomain)),
         'planId' => $this->planId,
         'billingPeriod' => $this->billingPeriod,
         'region' => $this->region,
      ];
   }

   public function clear(): void {
      $this->reset();
      $this->billingPeriod = 'monthly';
      $this->region = $this->defaultRegion();
   }

   public function defaultRegion(): string {
      return (string) config('tenancy.multi_region.default_region', 'us-east-1');
   }

   /**
    * @return list<string>
    */
   private function availableRegions(): array {
      $regions = config('tenancy.multi_region.regions', []);

      if (! is_array($regions) || $regions === []) {
         return [$this->defaultRegion()];
      }

      return array_values(array_filter(array_keys($regions), static fn(mixed $key): bool => is_string($key) && $key !== ''));
   }
}
