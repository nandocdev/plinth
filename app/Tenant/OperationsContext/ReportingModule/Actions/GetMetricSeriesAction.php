<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\ReportingModule\Actions;

use App\Tenant\OperationsContext\ReportingModule\DTOs\AnalyticsPeriodData;
use App\Tenant\OperationsContext\ReportingModule\DTOs\MetricSeriesData;
use App\Tenant\OperationsContext\ReportingModule\Enums\TenantMetricKey;
use App\Tenant\OperationsContext\ReportingModule\Models\TenantMetricSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class GetMetricSeriesAction {
   /** @return list<MetricSeriesData> */
   public function execute(AnalyticsPeriodData $period, TenantMetricKey ...$keys): array {
      $from = Carbon::parse($period->from)->startOfDay();
      $to = Carbon::parse($period->to)->endOfDay();

      $keyValues = array_map(static fn(TenantMetricKey $k): string => $k->value, $keys);

      /** @var Collection<int, TenantMetricSnapshot> $rows */
      $rows = TenantMetricSnapshot::query()
         ->whereBetween('snapshot_date', [$from->toDateString(), $to->toDateString()])
         ->whereIn('metric_key', $keyValues)
         ->orderBy('snapshot_date')
         ->get(['snapshot_date', 'metric_key', 'value']);

      $result = [];

      foreach ($keys as $key) {
         $series = $rows
            ->where('metric_key', $key->value)
            ->map(static fn(TenantMetricSnapshot $s): array => [
               'date' => $s->snapshot_date->toDateString(),
               'value' => (float) $s->value,
            ])
            ->values()
            ->all();

         $values = array_column($series, 'value');
         $total = array_sum($values);
         $count = count($values);

         $result[] = new MetricSeriesData(
            key: $key,
            points: $series,
            total: (float) $total,
            average: $count > 0 ? round($total / $count, 2) : 0.0,
            peak: $count > 0 ? (float) max($values) : 0.0,
         );
      }

      return $result;
   }
}
