<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\OperationsContext\WebhookModule\Models\IncomingWebhookToken;

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
