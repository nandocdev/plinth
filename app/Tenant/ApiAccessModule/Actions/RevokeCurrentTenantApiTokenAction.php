<?php

declare(strict_types=1);

namespace App\Tenant\ApiAccessModule\Actions;

use App\Tenant\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\DB;

final class RevokeCurrentTenantApiTokenAction {
   public function execute(User $user): void {
      DB::transaction(function () use ($user): void {
         $token = $user->currentAccessToken();

         if ($token !== null) {
            $token->delete();
         }
      });
   }
}
