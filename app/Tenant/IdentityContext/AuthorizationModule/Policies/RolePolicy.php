<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthorizationModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;

/**
 * Solo administradores del tenant pueden ver y gestionar la asignación de roles.
 */
final class RolePolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }

   public function manage(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }
}
