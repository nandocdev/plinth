<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Policies;

use App\Central\AuthenticationModule\Models\User;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;

final class PartnerWebhookEndpointPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null
         && ($user->hasRole('super_admin') || $user->can('admins.manage'));
   }

   public function create(User $user): bool {
      return $this->viewAny($user);
   }

   public function update(User $user, PartnerWebhookEndpoint $endpoint): bool {
      return $this->viewAny($user);
   }
}
