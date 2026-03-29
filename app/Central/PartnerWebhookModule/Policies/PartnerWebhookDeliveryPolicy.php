<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Policies;

use App\Central\AuthenticationModule\Models\User;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;

final class PartnerWebhookDeliveryPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null
         && ($user->hasRole('super_admin') || $user->can('admins.manage'));
   }

   public function retry(User $user, PartnerWebhookDelivery $delivery): bool {
      return $this->viewAny($user);
   }
}
