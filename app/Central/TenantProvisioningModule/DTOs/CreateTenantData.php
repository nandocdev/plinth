<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

use Illuminate\Support\Str;

final readonly class CreateTenantData {
   public function __construct(
      public string $name,
      public string $primaryDomain,
      public string $tenantId,
   ) {
   }

   public static function fromValues(string $name, string $primaryDomain): self {
      return new self(
         name: trim($name),
         primaryDomain: trim(Str::lower($primaryDomain)),
         tenantId: (string) Str::uuid(),
      );
   }
}
