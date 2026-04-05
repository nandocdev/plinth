<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\WebhookModule\Models\IncomingWebhookToken;

final class IncomingWebhookTokenPolicy {
   public function viewAny(User $user): bool {
      return $user->hasRole('admin', 'tenant');
   }

   public function create(User $user): bool {
      return $user->hasRole('admin', 'tenant');
   }

   public function delete(User $user, IncomingWebhookToken $token): bool {
      return $user->hasRole('admin', 'tenant');
   }
}
