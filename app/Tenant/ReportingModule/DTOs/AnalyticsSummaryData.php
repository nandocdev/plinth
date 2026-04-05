<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\DTOs;

final readonly class AnalyticsSummaryData {
   public function __construct(
      public int $usersTotal,
      public int $usersActiveMonth,
      public int $loginsDay,
      public float $filesStorageMb,
      public int $activityLogEntries,
      public int $webhookDeliveriesSuccess,
      public int $webhookDeliveriesFailed,
      public int $apiRequestsDay,
      /** UTC timestamp del último snapshot disponible */
      public ?string $lastSnapshotAt,
   ) {
   }
}
