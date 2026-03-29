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

   public string $brandName = '';

   public string $logoUrl = '';

   public string $primaryColor = '#f53003';

   public string $secondaryColor = '#ff4433';

   /**
    * @return array{name: string, primaryDomain: string, region: string, brandName: ?string, logoUrl: ?string, primaryColor: ?string, secondaryColor: ?string}
    */
   public function payload(): array {
      $this->validate([
         'name' => ['required', 'string', 'min:3', 'max:100'],
         'primaryDomain' => ['required', 'string', 'max:255', 'unique:domains,domain', 'regex:/^[a-z0-9][a-z0-9\-.]+[a-z0-9]$/'],
         'region' => ['required', 'string', Rule::in($this->availableRegions())],
         'brandName' => ['nullable', 'string', 'max:120'],
         'logoUrl' => ['nullable', 'url', 'max:2048'],
         'primaryColor' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
         'secondaryColor' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
      ]);

      return [
         'name' => trim($this->name),
         'primaryDomain' => trim(strtolower($this->primaryDomain)),
         'region' => $this->region,
         'brandName' => $this->brandName !== '' ? trim($this->brandName) : null,
         'logoUrl' => $this->logoUrl !== '' ? trim($this->logoUrl) : null,
         'primaryColor' => $this->primaryColor !== '' ? strtolower(trim($this->primaryColor)) : null,
         'secondaryColor' => $this->secondaryColor !== '' ? strtolower(trim($this->secondaryColor)) : null,
      ];
   }

   public function clear(): void {
      $this->reset();
      $this->region = $this->defaultRegion();
      $this->primaryColor = '#f53003';
      $this->secondaryColor = '#ff4433';
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
