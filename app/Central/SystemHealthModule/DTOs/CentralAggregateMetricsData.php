<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\DTOs;

final readonly class CentralAggregateMetricsData {
   public function __construct(
      public int $totalTenants,
      public int $activeTenants,
      public int $newTenantsLast7Days,
      public int $atRiskTenants,
      public int $criticalErrorsLast24h,
      public int $failedJobsCount,
      public int $activeSubscriptions,
      public int $trialingSubscriptions,
      public int $pastDueSubscriptions,
      public int $monthlyPaidInvoices,
      public int $monthlyRevenueUsdCents,
      public int $monthlyRecurringRevenueUsdCents,
   ) {
   }
}
