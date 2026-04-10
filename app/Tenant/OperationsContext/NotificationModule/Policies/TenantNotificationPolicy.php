<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\NotificationModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;

final class TenantNotificationPolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasAnyRole(['admin', 'manager', 'member']);
   }

   public function send(User $actor): bool {
      return $actor->hasAnyRole(['admin', 'manager']);
   }

   public function markRead(User $actor): bool {
      return $actor->hasAnyRole(['admin', 'manager', 'member']);
   }
}
