<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Actions;

use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

final class RevokeAdminRoleAction {
   public function execute(int $adminId): User {
      return DB::transaction(function () use ($adminId): User {
         /** @var User $admin */
         $admin = User::findOrFail($adminId);

         $admin->syncRoles([]);

         app(PermissionRegistrar::class)->forgetCachedPermissions();

         return $admin->refresh();
      });
   }
}
