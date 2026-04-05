<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\Actions;

use App\Tenant\ReportingModule\Enums\TenantMetricKey;
use App\Tenant\ReportingModule\Models\TenantMetricSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Calcula y persiste las métricas del día actual para el tenant.
 * Diseñada para ser idempotente: usa updateOrCreate.
 * Llamada desde TakeMetricSnapshotJob (nocturno o manual).
 */
final class TakeMetricSnapshotAction {
   public function execute(): void {
      $date = Carbon::today()->toDateString();

      $metrics = $this->computeMetrics();

      DB::transaction(function () use ($date, $metrics): void {
         foreach ($metrics as $key => $value) {
            TenantMetricSnapshot::query()->updateOrCreate(
               ['snapshot_date' => $date, 'metric_key' => $key],
               ['value' => $value],
            );
         }
      });
   }

   /** @return array<string, float> */
   private function computeMetrics(): array {
      $today = Carbon::today();
      $monthStart = Carbon::now()->startOfMonth();

      // Importa dinámicamente para no acoplar módulos. Si la tabla no existe,
      // la métrica queda en 0 sin romper el snapshot del resto.
      $metrics = [];

      // users_total
      try {
         $metrics[TenantMetricKey::UsersTotal->value] = (float) DB::table('users')->count();
      } catch (\Throwable) {
         $metrics[TenantMetricKey::UsersTotal->value] = 0.0;
      }

      // users_active_month (basado en activity_log updated_at o last_login si existe)
      try {
         $metrics[TenantMetricKey::UsersActiveMonth->value] = (float) DB::table('users')
            ->where('updated_at', '>=', $monthStart)
            ->count();
      } catch (\Throwable) {
         $metrics[TenantMetricKey::UsersActiveMonth->value] = 0.0;
      }

      // logins_day (cuenta entradas de activity_log con event = 'login' hoy)
      try {
         $query = DB::table('activity_log')
            ->whereDate('created_at', $today);

         if (Schema::hasColumn('activity_log', 'event')) {
            $query->where('event', 'login');
         }

         $metrics[TenantMetricKey::LoginsDay->value] = (float) $query->count();
      } catch (\Throwable) {
         $metrics[TenantMetricKey::LoginsDay->value] = 0.0;
      }

      // files_storage_mb (suma del tamaño de archivos si existe tabla media)
      try {
         if (Schema::hasTable('media')) {
            $bytes = (float) (DB::table('media')->sum('size') ?? 0);
            $metrics[TenantMetricKey::FilesStorageMb->value] = round($bytes / 1024 / 1024, 2);
            $metrics[TenantMetricKey::FilesUploaded->value] = (float) DB::table('media')->count();
         } else {
            $metrics[TenantMetricKey::FilesStorageMb->value] = 0.0;
            $metrics[TenantMetricKey::FilesUploaded->value] = 0.0;
         }
      } catch (\Throwable) {
         $metrics[TenantMetricKey::FilesStorageMb->value] = 0.0;
         $metrics[TenantMetricKey::FilesUploaded->value] = 0.0;
      }

      // activity_log_entries total
      try {
         $metrics[TenantMetricKey::ActivityLogEntries->value] = (float) DB::table('activity_log')->count();
      } catch (\Throwable) {
         $metrics[TenantMetricKey::ActivityLogEntries->value] = 0.0;
      }

      // webhook deliveries (hoy)
      try {
         if (Schema::hasTable('tenant_webhook_deliveries')) {
            $metrics[TenantMetricKey::WebhookDeliveriesSuccess->value] = (float) DB::table('tenant_webhook_deliveries')
               ->where('status', 'delivered')
               ->whereDate('delivered_at', $today)
               ->count();

            $metrics[TenantMetricKey::WebhookDeliveriesFailed->value] = (float) DB::table('tenant_webhook_deliveries')
               ->where('status', 'failed')
               ->whereDate('created_at', $today)
               ->count();
         } else {
            $metrics[TenantMetricKey::WebhookDeliveriesSuccess->value] = 0.0;
            $metrics[TenantMetricKey::WebhookDeliveriesFailed->value] = 0.0;
         }
      } catch (\Throwable) {
         $metrics[TenantMetricKey::WebhookDeliveriesSuccess->value] = 0.0;
         $metrics[TenantMetricKey::WebhookDeliveriesFailed->value] = 0.0;
      }

      // api_requests_day (personal_access_tokens o sanctum tokens usados hoy)
      try {
         if (Schema::hasTable('personal_access_tokens')) {
            $metrics[TenantMetricKey::ApiRequestsDay->value] = (float) DB::table('personal_access_tokens')
               ->whereDate('last_used_at', $today)
               ->count();
         } else {
            $metrics[TenantMetricKey::ApiRequestsDay->value] = 0.0;
         }
      } catch (\Throwable) {
         $metrics[TenantMetricKey::ApiRequestsDay->value] = 0.0;
      }

      Log::debug('TakeMetricSnapshotAction: métricas calculadas', ['metrics' => $metrics]);

      return $metrics;
   }
}
