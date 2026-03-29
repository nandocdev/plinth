<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\DTOs;

final readonly class QueueHealthData {
   public function __construct(
      public string $connection,
      public int $pendingJobs,
      public int $failedJobs,
      public bool $healthy,
   ) {
   }
}
