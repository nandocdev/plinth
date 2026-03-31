<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Actions;

use App\Tenant\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;

final class ConfirmTenantTwoFactorAction {
   /**
    * @throws ValidationException
    */
   public function execute(User $user, string $code): User {
      return DB::transaction(function () use ($user, $code): User {
         app(ConfirmTwoFactorAuthentication::class)($user, $code);

         return $user->refresh();
      });
   }
}
