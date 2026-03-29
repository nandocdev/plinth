<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Events;

use App\Central\DataExportModule\Models\CentralDataExport;

final readonly class CentralDataExportCompleted {
   public function __construct(
      public CentralDataExport $export,
   ) {
   }
}
