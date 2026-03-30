<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class PublicTenantRegistrationData {
   public function __construct(
      public string $companyName,
      public string $subdomain,
      public string $adminName,
      public string $adminEmail,
      public string $adminPassword,
      public int $planId,
   ) {
   }
}
