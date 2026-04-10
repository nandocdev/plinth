<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\IdentityContext\UserManagementModule\DTOs\CreateTenantUserData;
use App\Tenant\IdentityContext\UserManagementModule\Events\TenantUserCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateTenantUserAction {
   public function execute(CreateTenantUserData $data): User {
      return DB::transaction(function () use ($data): User {
         /** @var User $user */
         $user = User::query()->create([
            'name'     => $data->name,
            'email'    => $data->email,
            'password' => Hash::make($data->password),
            'status'   => 'active',
         ]);

         $user->assignRole($data->role->value);

         event(new TenantUserCreated($user));

         return $user;
      });
   }
}
