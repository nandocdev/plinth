<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\ExportImportModule\Policies;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;

final class TenantCsvTransferRunPolicy {
   public function viewAny(User $actor): bool {
      return $actor->hasRole('admin', 'tenant') || $actor->hasRole('manager', 'tenant');
   }

   public function export(User $actor): bool {
      return $actor->hasRole('admin', 'tenant') || $actor->hasRole('manager', 'tenant');
   }

   public function import(User $actor): bool {
      return $actor->hasRole('admin', 'tenant') || $actor->hasRole('manager', 'tenant');
   }
}
