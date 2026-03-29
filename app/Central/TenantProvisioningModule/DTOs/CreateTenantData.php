<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

use Illuminate\Support\Str;

final readonly class CreateTenantData {
   public function __construct(
      public string $name,
      public string $primaryDomain,
      public string $tenantId,
      public ?string $region = null,
      public ?string $brandName = null,
      public ?string $logoUrl = null,
      public ?string $primaryColor = null,
      public ?string $secondaryColor = null,
   ) {
   }

   public static function fromValues(
      string $name,
      string $primaryDomain,
      ?string $region = null,
      ?string $brandName = null,
      ?string $logoUrl = null,
      ?string $primaryColor = null,
      ?string $secondaryColor = null,
   ): self {
      return new self(
         name: trim($name),
         primaryDomain: trim(Str::lower($primaryDomain)),
         tenantId: (string) Str::uuid(),
         region: $region !== null && $region !== '' ? trim(Str::lower($region)) : null,
         brandName: $brandName !== null && $brandName !== '' ? trim($brandName) : null,
         logoUrl: $logoUrl !== null && $logoUrl !== '' ? trim($logoUrl) : null,
         primaryColor: $primaryColor !== null && $primaryColor !== '' ? trim(Str::lower($primaryColor)) : null,
         secondaryColor: $secondaryColor !== null && $secondaryColor !== '' ? trim(Str::lower($secondaryColor)) : null,
      );
   }
}
