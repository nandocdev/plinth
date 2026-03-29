<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class TenantProvisioningRegionData {
   public function __construct(
      public string $code,
      public string $label,
      public string $dbConnection,
   ) {
   }
}
