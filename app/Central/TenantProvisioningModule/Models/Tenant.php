<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Models;

use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

final class Tenant extends BaseTenant implements TenantWithDatabase {
   use HasDatabase;

   /**
    * @return array<string, mixed>
    */
   public function metadata(): array {
      $metadata = $this->getAttributes();

      unset(
         $metadata['id'],
         $metadata['created_at'],
         $metadata['updated_at'],
         $metadata['data']
      );

      return $metadata;
   }

   public function status(): string {
      $status = $this->metadata()['status'] ?? 'active';

      return is_string($status) ? $status : 'active';
   }

   public function displayName(): string {
      $name = $this->metadata()['name'] ?? $this->id;

      return is_string($name) ? $name : (string) $this->id;
   }

   public function region(): string {
      $region = $this->metadata()['region'] ?? config('tenancy.multi_region.default_region', 'us-east-1');

      return is_string($region) && $region !== ''
         ? $region
         : (string) config('tenancy.multi_region.default_region', 'us-east-1');
   }

   /**
    * @return array<string, mixed>
    */
   public function branding(): array {
      $branding = $this->metadata()['branding'] ?? [];

      return is_array($branding) ? $branding : [];
   }

   public function brandName(): string {
      $value = $this->branding()['brand_name'] ?? $this->displayName();

      return is_string($value) && $value !== '' ? $value : $this->displayName();
   }

   public function logoUrl(): ?string {
      $value = $this->branding()['logo_url'] ?? null;

      if (! is_string($value) || $value === '') {
         return null;
      }

      return $value;
   }

   public function primaryColor(): string {
      $value = $this->branding()['primary_color'] ?? '#f53003';

      return is_string($value) && $value !== '' ? $value : '#f53003';
   }

   public function secondaryColor(): string {
      $value = $this->branding()['secondary_color'] ?? '#ff4433';

      return is_string($value) && $value !== '' ? $value : '#ff4433';
   }

   public function subscription(): HasOne {
      return $this->hasOne(TenantSubscription::class, 'tenant_id', 'id');
   }

   public function domains(): HasMany {
      return $this->hasMany(Domain::class, 'tenant_id', 'id');
   }

   public function recoverySnapshots(): HasMany {
      return $this->hasMany(TenantRecoverySnapshot::class, 'tenant_id', 'id');
   }
}
