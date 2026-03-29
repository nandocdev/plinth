<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Actions;

use App\Central\AdminAuthorizationModule\DTOs\AssignAdminRoleData;
use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Asigna un rol a un admin central.
 * Un admin puede tener UN solo rol central a la vez — syncRoles() reemplaza el rol anterior.
 */
final class AssignAdminRoleAction {
   public function execute(AssignAdminRoleData $data): User {
      return DB::transaction(function () use ($data): User {
         /** @var User $admin */
         $admin = User::findOrFail($data->adminId);

         // Reemplaza cualquier rol central previo
         $admin->syncRoles([$data->role->value]);

         // Invalidar cache de permisos para este usuario
         app(PermissionRegistrar::class)->forgetCachedPermissions();

         return $admin->refresh();
      });
   }
}
