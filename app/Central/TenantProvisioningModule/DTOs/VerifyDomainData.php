<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class VerifyDomainData {
   public function __construct(
      public string $tenantId,
      public int $domainId,
      public bool $verified,
   ) {
   }
}