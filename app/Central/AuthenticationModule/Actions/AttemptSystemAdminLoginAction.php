<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Actions;

use App\Central\AuthenticationModule\DTOs\SystemAdminLoginData;
use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\Hash;

final class AttemptSystemAdminLoginAction {
   public function execute(SystemAdminLoginData $data): ?User {
      $admin = User::query()->where('email', $data->email)->first();

      if (! $admin) {
         return null;
      }

      if (! Hash::check($data->password, $admin->password)) {
         return null;
      }

      return $admin;
   }
}
