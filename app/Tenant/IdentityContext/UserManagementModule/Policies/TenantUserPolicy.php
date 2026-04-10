<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;

/**
 * Solo los usuarios con rol 'admin' en el tenant pueden gestionar otros usuarios.
 * El usuario autenticado se resuelve siempre desde guard 'tenant'.
 */
final class TenantUserPolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }

   public function view(User $actor, User $target): bool {
      return $actor->hasRole('admin', 'tenant');
   }

   public function create(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }

   public function update(User $actor, User $target): bool {
      // Admin puede actualizar cualquier usuario excepto a sí mismo cambiarle el rol
      return $actor->hasRole('admin', 'tenant');
   }

   public function delete(User $actor, User $target): bool {
      // Admin puede eliminar usuarios, pero no a sí mismo (controlado también en la Action)
      return $actor->hasRole('admin', 'tenant')
         && $actor->id !== $target->id;
   }
}
