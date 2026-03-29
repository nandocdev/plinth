<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class TenantImpersonationSessionData {
   public function __construct(
      public string $tenantId,
      public int $impersonatorUserId,
      public string $targetDomain,
   ) {
   }
}
