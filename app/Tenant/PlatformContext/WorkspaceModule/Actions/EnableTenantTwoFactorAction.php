<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

final class EnableTenantTwoFactorAction {
   public function execute(User $user): User {
      return DB::transaction(function () use ($user): User {
         app(EnableTwoFactorAuthentication::class)($user, true);

         return $user->refresh();
      });
   }
}
