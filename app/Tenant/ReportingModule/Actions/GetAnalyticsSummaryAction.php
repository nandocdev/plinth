<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\Actions;

use App\Tenant\ReportingModule\DTOs\AnalyticsSummaryData;
use App\Tenant\ReportingModule\Enums\TenantMetricKey;
use App\Tenant\ReportingModule\Models\TenantMetricSnapshot;
use Illuminate\Support\Carbon;

final class GetAnalyticsSummaryAction {
   public function execute(): AnalyticsSummaryData {
      $today = Carbon::today()->toDateString();

      // Último snapshot disponible: si hoy no hay datos usa el más reciente
      /** @var TenantMetricSnapshot|null $latest */
      $latest = TenantMetricSnapshot::query()
         ->orderByDesc('snapshot_date')
         ->first(['snapshot_date']);

      $referenceDate = $latest?->snapshot_date?->toDateString() ?? $today;

      $snapshots = TenantMetricSnapshot::query()
         ->where('snapshot_date', $referenceDate)
         ->get(['metric_key', 'value'])
         ->keyBy('metric_key');

      $get = static function (TenantMetricKey $key) use ($snapshots): float {
         /** @var TenantMetricSnapshot|null $snap */
         $snap = $snapshots->get($key->value);
         return (float) ($snap?->value ?? 0);
      };

      return new AnalyticsSummaryData(
         usersTotal: (int) $get(TenantMetricKey::UsersTotal),
         usersActiveMonth: (int) $get(TenantMetricKey::UsersActiveMonth),
         loginsDay: (int) $get(TenantMetricKey::LoginsDay),
         filesStorageMb: round($get(TenantMetricKey::FilesStorageMb), 2),
         activityLogEntries: (int) $get(TenantMetricKey::ActivityLogEntries),
         webhookDeliveriesSuccess: (int) $get(TenantMetricKey::WebhookDeliveriesSuccess),
         webhookDeliveriesFailed: (int) $get(TenantMetricKey::WebhookDeliveriesFailed),
         apiRequestsDay: (int) $get(TenantMetricKey::ApiRequestsDay),
         lastSnapshotAt: $latest?->snapshot_date?->toDateString(),
      );
   }
}
