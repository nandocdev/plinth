<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\DTOs;

final readonly class GlobalLogEntryData {
   public function __construct(
      public string $timestamp,
      public string $level,
      public string $message,
      public ?string $tenantId,
      public string $raw,
   ) {
   }
}
