<?php

declare(strict_types=1);

namespace App\Tenant\ActivityLogModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;

final class TenantActivityLogEntryPolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }
}
