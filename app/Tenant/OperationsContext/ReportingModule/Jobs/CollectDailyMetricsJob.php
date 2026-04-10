<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\ReportingModule\Jobs;

use App\Shared\Infrastructure\Jobs\Middleware\EnsureTenantContext;
use App\Tenant\OperationsContext\ReportingModule\Actions\TakeMetricSnapshotAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class CollectDailyMetricsJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public int $tries   = 3;
   public int $timeout = 120;

   public function __construct() {
      $this->onQueue('default');
   }

   public function uniqueId(): string {
      return 'tenant-collect-metrics-' . (tenant()?->id ?? 'unknown') . '-' . now()->toDateString();
   }

   /**
    * @return array<int, EnsureTenantContext>
    */
   public function middleware(): array {
      return [new EnsureTenantContext];
   }

   public function handle(TakeMetricSnapshotAction $action): void {
      $action->execute();
   }

   public function failed(\Throwable $e): void {
      Log::error('CollectDailyMetricsJob fallido', [
         'tenant_id' => tenant()?->id,
         'error'     => $e->getMessage(),
      ]);
   }
}
