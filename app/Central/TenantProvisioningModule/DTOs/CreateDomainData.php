<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

use Illuminate\Support\Str;

final readonly class CreateDomainData {
   public function __construct(
      public string $tenantId,
      public string $domain,
   ) {
   }

   public static function fromValues(string $tenantId, string $domain): self {
      return new self(
         tenantId: trim($tenantId),
         domain: trim(Str::lower($domain)),
      );
   }
}
