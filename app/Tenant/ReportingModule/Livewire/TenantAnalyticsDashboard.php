<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\Livewire;

use App\Tenant\ReportingModule\Actions\GetAnalyticsSummaryAction;
use App\Tenant\ReportingModule\Actions\GetMetricSeriesAction;
use App\Tenant\ReportingModule\DTOs\AnalyticsPeriodData;
use App\Tenant\ReportingModule\DTOs\AnalyticsSummaryData;
use App\Tenant\ReportingModule\DTOs\MetricSeriesData;
use App\Tenant\ReportingModule\Enums\TenantMetricKey;
use App\Tenant\ReportingModule\Jobs\CollectDailyMetricsJob;
use App\Tenant\ReportingModule\Livewire\Forms\AnalyticsPeriodForm;
use App\Tenant\ReportingModule\Models\TenantMetricSnapshot;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Analytics')]
final class TenantAnalyticsDashboard extends Component {
   use AuthorizesRequests;

   public AnalyticsPeriodForm $periodForm;

   public bool $collectingMetrics = false;

   public function mount(): void {
      $this->authorize('viewAny', TenantMetricSnapshot::class);
      $this->periodForm->setDefaults();
   }

   public function applyPeriod(): void {
      $this->periodForm->validate();
   }

   public function resetPeriod(): void {
      $this->periodForm->setDefaults();
   }

   public function collectNow(): void {
      $this->authorize('collectMetrics', TenantMetricSnapshot::class);
      CollectDailyMetricsJob::dispatch();
      $this->collectingMetrics = true;
   }

   public function render(
      GetAnalyticsSummaryAction $getSummary,
      GetMetricSeriesAction     $getSeries,
   ): View {
      $payload = $this->periodForm->payload();
      $period  = new AnalyticsPeriodData(
         from: $payload['from'],
         to: $payload['to'],
         groupBy: $payload['groupBy'],
      );

      /** @var AnalyticsSummaryData $summary */
      $summary = $getSummary->execute();

      /** @var list<MetricSeriesData> $series */
      $series = $getSeries->execute(
         $period,
         TenantMetricKey::UsersTotal,
         TenantMetricKey::LoginsDay,
         TenantMetricKey::ActivityLogEntries,
         TenantMetricKey::WebhookDeliveriesSuccess,
         TenantMetricKey::WebhookDeliveriesFailed,
      );

      $seriesJson = json_encode(
         array_map(
            static fn(MetricSeriesData $s): array => [
               'key'     => $s->key->value,
               'label'   => $s->key->label(),
               'unit'    => $s->key->unit(),
               'total'   => $s->total,
               'average' => $s->average,
               'peak'    => $s->peak,
               'points'  => $s->points,
            ],
            $series,
         ),
         JSON_THROW_ON_ERROR,
      );

      return view('reporting::livewire.tenant-analytics-dashboard', [
         'summary'    => $summary,
         'seriesJson' => $seriesJson,
         'period'     => $period,
      ]);
   }
}
