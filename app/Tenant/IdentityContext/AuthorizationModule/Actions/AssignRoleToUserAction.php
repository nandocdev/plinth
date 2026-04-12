<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthorizationModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole;
use Illuminate\Support\Facades\DB;

/**
 * Reasigna el rol de un usuario dentro del tenant activo.
 * Reemplaza cualquier rol previo (syncRoles garantiza exclusividad).
 */
final class AssignRoleToUserAction {
   public function execute(User $target, TenantRole $role): User {
      return DB::transaction(function () use ($target, $role): User {
         $target->syncRoles([$role->value]);

         return $target->refresh();
      });
   }
}
