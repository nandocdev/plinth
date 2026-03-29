<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class QueueTenantRestoreData {
   public function __construct(
      public string $tenantId,
      public int $sourceSnapshotId,
      public int $requestedByUserId,
   ) {
   }

   public static function fromValues(string $tenantId, int $sourceSnapshotId, int $requestedByUserId): self {
      return new self(
         tenantId: $tenantId,
         sourceSnapshotId: $sourceSnapshotId,
         requestedByUserId: $requestedByUserId,
      );
   }
}
