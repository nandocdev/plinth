<?php

declare(strict_types=1);

namespace App\Tenant\UserManagementModule\Actions;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\UserManagementModule\DTOs\UpdateTenantUserData;
use Illuminate\Support\Facades\DB;

final class UpdateTenantUserAction {
   public function execute(User $user, UpdateTenantUserData $data): User {
      return DB::transaction(function () use ($user, $data): User {
         $user->update([
            'name'   => $data->name,
            'email'  => $data->email,
            'status' => $data->status->value,
         ]);

         // Reemplazar todos los roles con el nuevo rol asignado
         $user->syncRoles([$data->role->value]);

         return $user->refresh();
      });
   }
}
