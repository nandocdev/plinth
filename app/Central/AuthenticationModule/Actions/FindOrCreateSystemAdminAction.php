<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Actions;

use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;

final class FindOrCreateSystemAdminAction {
   public function execute(string $name, string $email, string $password): User {
      /** @var User $user */
      $user = DB::connection('central')->transaction(function () use ($name, $email, $password): User {
         /** @var User $existing */
         $existing = User::query()->where('email', $email)->first();

         if ($existing !== null) {
            return $existing;
         }

         /** @var User $created */
         $created = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
         ]);

         return $created;
      });

      return $user;
   }
}
