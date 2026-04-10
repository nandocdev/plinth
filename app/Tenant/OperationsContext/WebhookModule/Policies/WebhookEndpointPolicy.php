<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\OperationsContext\WebhookModule\Models\WebhookEndpoint;

final class WebhookEndpointPolicy {
   public function viewAny(User $user): bool {
      return $user->hasRole('admin', 'tenant');
   }

   public function create(User $user): bool {
      return $user->hasRole('admin', 'tenant');
   }

   public function update(User $user, WebhookEndpoint $endpoint): bool {
      return $user->hasRole('admin', 'tenant');
   }

   public function delete(User $user, WebhookEndpoint $endpoint): bool {
      return $user->hasRole('admin', 'tenant');
   }
}
