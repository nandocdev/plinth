<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

final class DisableTenantTwoFactorAction {
   public function execute(User $user): User {
      return DB::transaction(function () use ($user): User {
         app(DisableTwoFactorAuthentication::class)($user);

         return $user->refresh();
      });
   }
}
