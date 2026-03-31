<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Actions;

use App\Tenant\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

final class RegenerateTenantTwoFactorRecoveryCodesAction {
   public function execute(User $user): User {
      return DB::transaction(function () use ($user): User {
         app(GenerateNewRecoveryCodes::class)($user);

         return $user->refresh();
      });
   }
}
