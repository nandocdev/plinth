<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\DTOs;

final readonly class TenantDashboardData {
   public function __construct(
      public string $tenantId,
      public string $tenantName,
      public string $tenantDomain,
      public string $tenantRegion,
      public string $primaryColor,
      public string $secondaryColor,
      public ?string $logoUrl,
      public string $userName,
      public string $userEmail,
   ) {
   }
}
