<?php

declare(strict_types=1);

namespace App\Tenant\ApiAccessModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;

final class TenantApiTokenPolicy {
   public function issue(User $user): bool {
      return $user->hasAnyRole(['admin', 'manager', 'member']);
   }

   public function revoke(User $user): bool {
      return $user->hasAnyRole(['admin', 'manager', 'member']);
   }

   public function viewSelf(User $user): bool {
      return $user->hasAnyRole(['admin', 'manager', 'member']);
   }
}
