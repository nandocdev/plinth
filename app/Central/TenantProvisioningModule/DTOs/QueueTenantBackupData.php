<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class QueueTenantBackupData {
   public function __construct(
      public string $tenantId,
      public int $requestedByUserId,
   ) {
   }

   public static function fromValues(string $tenantId, int $requestedByUserId): self {
      return new self(
         tenantId: $tenantId,
         requestedByUserId: $requestedByUserId,
      );
   }
}
