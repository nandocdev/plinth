<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class TenantBrandingData {
   public function __construct(
      public string $tenantId,
      public ?string $brandName,
      public ?string $logoUrl,
      public ?string $primaryColor,
      public ?string $secondaryColor,
   ) {
   }
}
