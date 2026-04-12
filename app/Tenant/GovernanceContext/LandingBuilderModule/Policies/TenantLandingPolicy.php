<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;

final class TenantLandingPolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }

   public function update(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }
}
