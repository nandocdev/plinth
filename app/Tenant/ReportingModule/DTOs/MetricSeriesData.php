<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\DTOs;

use App\Tenant\ReportingModule\Enums\TenantMetricKey;

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
