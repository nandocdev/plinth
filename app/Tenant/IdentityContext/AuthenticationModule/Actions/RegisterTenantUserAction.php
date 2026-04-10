<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthenticationModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\DTOs\RegisterTenantUserData;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

final class RegisterTenantUserAction {
   public function execute(RegisterTenantUserData $data): User {
      /** @var User $user */
      $user = DB::transaction(function () use ($data): User {
         /** @var User $created */
         $created = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'email_verified_at' => now(),
         ]);

         return $created;
      });

      event(new Registered($user));

      return $user;
   }
}
