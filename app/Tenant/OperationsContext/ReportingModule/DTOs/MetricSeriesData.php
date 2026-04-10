<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\ReportingModule\DTOs;

use App\Tenant\OperationsContext\ReportingModule\Enums\TenantMetricKey;

final readonly class MetricSeriesData {
   /** @param list<array{date: string, value: float}> $points */
   public function __construct(
      public TenantMetricKey $key,
      public array $points,
      public float $total,
      public float $average,
      public float $peak,
   ) {
   }
}
