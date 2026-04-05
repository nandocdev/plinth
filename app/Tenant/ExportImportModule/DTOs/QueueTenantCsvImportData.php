<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\DTOs;

final readonly class QueueTenantCsvImportData {
   public function __construct(
      public string $tenantId,
      public ?int $requestedByUserId,
      public string $sourceDisk,
      public string $sourcePath,
   ) {
   }
}
