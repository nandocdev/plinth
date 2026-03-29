<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Policies;

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Models\TenantSubscription;

final class TenantSubscriptionPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function create(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function update(User $user, TenantSubscription $subscription): bool {
      return $user->email_verified_at !== null;
   }

   public function delete(User $user, TenantSubscription $subscription): bool {
      return $user->email_verified_at !== null;
   }
}
