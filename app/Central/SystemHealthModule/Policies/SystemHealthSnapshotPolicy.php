<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\Policies;

use App\Central\AuthenticationModule\Models\User;

final class SystemHealthSnapshotPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null;
   }
}
