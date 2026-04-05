<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\Policies;

use App\Tenant\AuthenticationModule\Models\User;

final class ReportingPolicy {
   /** Cualquier usuario autenticado puede ver analytics. */
   public function viewAny(User $user): bool {
      return true;
   }

   /** Sólo admins pueden lanzar colección manual de métricas. */
   public function collectMetrics(User $user): bool {
      return $user->hasRole('admin');
   }
}
