<?php

declare(strict_types=1);

namespace App\Tenant\QueueModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;

final class TenantQueueContextRunPolicy {
   public function viewAny(User $user): bool {
      return $user->hasAnyRole(['admin', 'manager']);
   }

   public function create(User $user): bool {
      return $user->hasAnyRole(['admin', 'manager']);
   }
}
