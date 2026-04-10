<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\ExportImportModule\DTOs;

final readonly class QueueTenantCsvImportData {
   public function __construct(
      public string $tenantId,
      public ?int $requestedByUserId,
      public string $sourceDisk,
      public string $sourcePath,
   ) {
   }
}
