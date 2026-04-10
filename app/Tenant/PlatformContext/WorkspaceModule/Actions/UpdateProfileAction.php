<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\PlatformContext\WorkspaceModule\DTOs\UpdateProfileData;
use Illuminate\Support\Facades\DB;

final class UpdateProfileAction {
   public function execute(User $user, UpdateProfileData $dto): User {
      return DB::transaction(function () use ($user, $dto): User {
         // Si el email cambia, invalidar la verificación
         if ($user->email !== $dto->email) {
            $user->email_verified_at = null;
         }

         $user->name  = $dto->name;
         $user->email = $dto->email;
         $user->save();

         return $user->refresh();
      });
   }
}
