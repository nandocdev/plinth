<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Policies;

use App\Central\AuthenticationModule\Models\User;

final class ActivityLogEntryPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null
         && $user->can('logs.view');
   }
}
