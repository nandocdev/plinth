<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class CreateTenantForm extends Form {
   #[Validate('required|string|min:3|max:100')]
   public string $name = '';

   #[Validate('required|string|max:255|unique:domains,domain|regex:/^[a-z0-9][a-z0-9\-.]+[a-z0-9]$/')]
   public string $primaryDomain = '';

   public string $region = '';

   /**
    * @return array{name: string, primaryDomain: string, region: string}
    */
   public function payload(): array {
      $this->validate([
         'name' => ['required', 'string', 'min:3', 'max:100'],
         'primaryDomain' => ['required', 'string', 'max:255', 'unique:domains,domain', 'regex:/^[a-z0-9][a-z0-9\-.]+[a-z0-9]$/'],
         'region' => ['required', 'string', Rule::in($this->availableRegions())],
      ]);

      return [
         'name' => trim($this->name),
         'primaryDomain' => trim(strtolower($this->primaryDomain)),
         'region' => $this->region,
      ];
   }

   public function clear(): void {
      $this->reset();
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
