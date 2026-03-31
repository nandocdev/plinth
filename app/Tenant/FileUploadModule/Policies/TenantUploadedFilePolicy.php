<?php

declare(strict_types=1);

namespace App\Tenant\FileUploadModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;

final class TenantUploadedFilePolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasRole('admin', 'tenant') || $actor->hasRole('manager', 'tenant');
   }

   public function create(User $actor): bool {
      return $actor->hasRole('admin', 'tenant') || $actor->hasRole('manager', 'tenant');
   }

   public function delete(User $actor): bool {
      return $actor->hasRole('admin', 'tenant') || $actor->hasRole('manager', 'tenant');
   }
}
