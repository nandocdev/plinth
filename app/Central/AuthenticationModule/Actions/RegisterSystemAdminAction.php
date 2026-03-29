<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Actions;

use App\Central\AuthenticationModule\DTOs\RegisterSystemAdminData;
use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;

final class RegisterSystemAdminAction {
   public function execute(RegisterSystemAdminData $data): User {
      /** @var User $user */
      $user = DB::transaction(function () use ($data): User {
         return User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
         ]);
      });

      return $user;
   }
}
