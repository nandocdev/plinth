<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\DTOs;

final readonly class StorageHealthData {
   public function __construct(
      public int $appBytes,
      public int $logsBytes,
      public int $frameworkBytes,
      public int $totalBytes,
      public string $formattedTotal,
   ) {
   }
}
