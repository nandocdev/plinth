<?php

declare(strict_types=1);

namespace App\Tenant\CustomDomainModule\DTOs;

final readonly class CreateCustomDomainData {
   public function __construct(
      public string $tenantId,
      public string $domain,
   ) {
   }
}
