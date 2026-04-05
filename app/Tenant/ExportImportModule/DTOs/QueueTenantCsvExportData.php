<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\DTOs;

final readonly class QueueTenantCsvExportData {
   public function __construct(
      public string $tenantId,
      public ?int $requestedByUserId,
   ) {
   }
}
