<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\CustomDomainModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;

final class TenantCustomDomainPolicy {
   public function viewAny(User $user): bool {
      return true;
   }

   public function manage(User $user): bool {
      return $user->hasRole('admin', 'tenant');
   }
}
