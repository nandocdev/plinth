<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire\Forms;

use App\Central\TenantProvisioningModule\Models\Tenant;
use Livewire\Form;

final class TenantBrandingForm extends Form {
   public string $tenantId = '';

   public string $brandName = '';

   public string $logoUrl = '';

   public string $primaryColor = '#f53003';

   public string $secondaryColor = '#ff4433';

   /**
    * @return array{tenantId: string, brandName: ?string, logoUrl: ?string, primaryColor: ?string, secondaryColor: ?string}
    */
   public function payload(): array {
      $this->validate([
         'tenantId' => ['required', 'string', 'exists:tenants,id'],
         'brandName' => ['nullable', 'string', 'max:120'],
         'logoUrl' => ['nullable', 'url', 'max:2048'],
         'primaryColor' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
         'secondaryColor' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
      ]);

      return [
         'tenantId' => $this->tenantId,
         'brandName' => $this->brandName !== '' ? trim($this->brandName) : null,
         'logoUrl' => $this->logoUrl !== '' ? trim($this->logoUrl) : null,
         'primaryColor' => $this->primaryColor !== '' ? strtolower(trim($this->primaryColor)) : null,
         'secondaryColor' => $this->secondaryColor !== '' ? strtolower(trim($this->secondaryColor)) : null,
      ];
   }

   public function fillFromTenant(Tenant $tenant): void {
      $branding = $tenant->branding();

      $this->tenantId = (string) $tenant->id;
      $this->brandName = (string) ($branding['brand_name'] ?? '');
      $this->logoUrl = (string) ($branding['logo_url'] ?? '');
      $this->primaryColor = (string) ($branding['primary_color'] ?? '#f53003');
      $this->secondaryColor = (string) ($branding['secondary_color'] ?? '#ff4433');
   }
}
