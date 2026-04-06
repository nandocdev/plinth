<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\Actions;

use App\Central\SystemHealthModule\DTOs\ConnectionHealthData;
use App\Central\SystemHealthModule\DTOs\SystemHealthFilterData;
use App\Central\SystemHealthModule\DTOs\SystemHealthSnapshotData;

final class FilterSystemHealthSnapshotAction {
   public function execute(SystemHealthSnapshotData $snapshot, SystemHealthFilterData $filter): SystemHealthSnapshotData {
      $connections = array_filter(
         $snapshot->connections,
         function (ConnectionHealthData $connection) use ($filter): bool {
            if ($filter->connectionFilter !== 'all' && $connection->name !== $filter->connectionFilter) {
               return false;
            }

            if ($filter->onlyUnhealthy && $connection->ok) {
               return false;
            }

            return true;
         },
      );

      return new SystemHealthSnapshotData(
         connections: array_values($connections),
         centralMetrics: $snapshot->centralMetrics,
         queue: $snapshot->queue,
         storage: $snapshot->storage,
         generatedAt: $snapshot->generatedAt,
      );
   }
}
