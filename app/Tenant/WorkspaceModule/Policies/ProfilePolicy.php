<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;

final class ProfilePolicy {
   public function viewAny(User $authUser): bool {
      return $authUser->hasRole('admin', 'tenant');
   }

   /** Cualquier usuario puede ver su perfil; admin puede ver cualquier usuario. */
   public function view(User $authUser, User $targetUser): bool {
      return $authUser->id === $targetUser->id
         || $authUser->hasRole('admin', 'tenant');
   }

   /** Solo admin puede crear usuarios. */
   public function create(User $authUser): bool {
      return $authUser->hasRole('admin', 'tenant');
   }

   /** El propio usuario o admin pueden actualizar. */
   public function update(User $authUser, User $targetUser): bool {
      return $authUser->id === $targetUser->id
         || $authUser->hasRole('admin', 'tenant');
   }

   /** Solo admin puede eliminar y nunca auto-eliminarse. */
   public function delete(User $authUser, User $targetUser): bool {
      return $authUser->hasRole('admin', 'tenant')
         && $authUser->id !== $targetUser->id;
   }
}
