<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\PlatformContext\WorkspaceModule\DTOs\UpdatePasswordData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class UpdatePasswordAction {
   /**
    * @throws ValidationException
    */
   public function execute(User $user, UpdatePasswordData $dto): void {
      if (! Hash::check($dto->currentPassword, $user->password)) {
         throw ValidationException::withMessages([
            'updatePasswordForm.current_password' => __('La contraseña actual no es correcta.'),
         ]);
      }

      DB::transaction(function () use ($user, $dto): void {
         $user->password = Hash::make($dto->password);
         $user->save();
      });
   }
}
