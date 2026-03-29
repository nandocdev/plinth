<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Policies;

use App\Central\AuthenticationModule\Models\User;

/**
 * Policy para la gestión de roles/permisos de admins centrales.
 * Solo super_admin puede asignar o revocar roles.
 */
final class AdminRolePolicy {
   public function viewAny(User $user): bool {
      return $user->hasAnyRole(['super_admin', 'support_admin', 'billing_admin', 'readonly_admin']);
   }

   public function assign(User $user): bool {
      return $user->hasRole('super_admin');
   }

   public function revoke(User $user): bool {
      return $user->hasRole('super_admin');
   }
}
