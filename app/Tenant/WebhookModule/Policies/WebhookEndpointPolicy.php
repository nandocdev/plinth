<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\WebhookModule\Models\WebhookEndpoint;

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
