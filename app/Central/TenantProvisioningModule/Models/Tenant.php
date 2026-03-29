<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Models;

use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

final class Tenant extends BaseTenant {
   /**
    * @return array<string, mixed>
    */
   public function metadata(): array {
      $data = $this->getAttribute('data');

      return is_array($data) ? $data : [];
   }

   public function status(): string {
      $status = $this->metadata()['status'] ?? 'active';

      return is_string($status) ? $status : 'active';
   }

   public function displayName(): string {
      $name = $this->metadata()['name'] ?? $this->id;

      return is_string($name) ? $name : (string) $this->id;
   }

   public function subscription(): HasOne {
      return $this->hasOne(TenantSubscription::class, 'tenant_id', 'id');
   }
}
