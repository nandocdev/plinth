<?php

declare(strict_types=1);

namespace App\Tenant\SettingsModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\SettingsModule\Models\TenantSetting;

final class TenantSettingsPolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }

   public function view(User $actor, TenantSetting $settings): bool {
      return $actor->hasRole('admin', 'tenant');
   }

   public function update(User $actor): bool {
      return $actor->hasRole('admin', 'tenant');
   }
}
