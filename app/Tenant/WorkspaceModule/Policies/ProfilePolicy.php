<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;

final class ProfilePolicy {
   /** Cualquier usuario autenticado puede ver su propio perfil. */
   public function view(User $authUser, User $targetUser): bool {
      return $authUser->id === $targetUser->id;
   }

   /** Solo el propio usuario puede actualizar su perfil. */
   public function update(User $authUser, User $targetUser): bool {
      return $authUser->id === $targetUser->id;
   }
}
