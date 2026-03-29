<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class DeleteDomainData {
   public function __construct(
      public string $tenantId,
      public int $domainId,
   ) {
   }
}
