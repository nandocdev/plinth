<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Policies;

use App\Central\AuthenticationModule\Models\User;

final class SystemAdminPolicy {
   public function accessCentralPanel(User $user): bool {
      return $user->email_verified_at !== null;
   }
}
