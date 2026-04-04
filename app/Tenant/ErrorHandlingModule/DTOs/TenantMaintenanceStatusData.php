<?php

declare(strict_types=1);

namespace App\Tenant\ErrorHandlingModule\DTOs;

final readonly class TenantMaintenanceStatusData {
   public function __construct(
      public bool $isInMaintenance,
      public string $tenantId,
      public string $tenantName,
      public ?string $message = null,
   ) {
   }
}
