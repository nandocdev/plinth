<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\AddonsModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;

final class TenantAddonPolicy {
   /** Cualquier usuario autenticado puede ver el catálogo. */
   public function viewAny(User $user): bool {
      return true;
   }

   /** Solo admins pueden instalar, desinstalar o togglear addons. */
   public function manage(User $user): bool {
      return $user->hasRole('admin', 'tenant');
   }
}
