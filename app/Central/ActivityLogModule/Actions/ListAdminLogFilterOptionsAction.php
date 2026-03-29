<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Actions;

use App\Central\AuthenticationModule\Models\User;

final class ListAdminLogFilterOptionsAction {
   /**
    * @return array<int, array{id: int, name: string, email: string}>
    */
   public function execute(): array {
      return User::query()
         ->select(['id', 'name', 'email'])
         ->orderBy('name')
         ->limit(200)
         ->get()
         ->map(fn(User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
         ])
         ->values()
         ->all();
   }
}
