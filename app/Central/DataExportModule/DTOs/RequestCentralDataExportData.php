<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\DTOs;

final readonly class RequestCentralDataExportData {
   public function __construct(
      public string $tenantId,
      public int $requestedByUserId,
      public bool $includeActivityLog,
   ) {
   }
}
