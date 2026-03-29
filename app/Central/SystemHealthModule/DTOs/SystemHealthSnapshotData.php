<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\DTOs;

final readonly class SystemHealthSnapshotData {
   /**
    * @param list<ConnectionHealthData> $connections
    */
   public function __construct(
      public array $connections,
      public QueueHealthData $queue,
      public StorageHealthData $storage,
      public string $generatedAt,
   ) {
   }
}
