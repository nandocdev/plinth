<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Policies;

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Models\Tenant;

final class TenantPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function create(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function update(User $user, Tenant $tenant): bool {
      return $user->email_verified_at !== null;
   }

   public function delete(User $user, Tenant $tenant): bool {
      return $user->email_verified_at !== null;
   }

   public function impersonate(User $user, Tenant $tenant): bool {
      return $user->email_verified_at !== null && $tenant->status() !== 'suspended';
   }

   public function backup(User $user, Tenant $tenant): bool {
      return $user->email_verified_at !== null;
   }

   public function restore(User $user, Tenant $tenant): bool {
      return $user->email_verified_at !== null;
   }
}
