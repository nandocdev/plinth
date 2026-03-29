<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Policies;

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Models\Domain;

final class DomainPolicy {
   public function create(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function update(User $user, Domain $domain): bool {
      return $user->email_verified_at !== null;
   }

   public function delete(User $user, Domain $domain): bool {
      return $user->email_verified_at !== null;
   }
}
