<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Events;

use App\Central\TenantProvisioningModule\Models\TenantRecoverySnapshot;

final readonly class TenantRecoverySnapshotCompleted {
   public function __construct(
      public TenantRecoverySnapshot $snapshot,
   ) {
   }
}
