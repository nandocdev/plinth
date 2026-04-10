<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\ExportImportModule\Jobs;

use App\Shared\Infrastructure\Jobs\Middleware\EnsureTenantContext;
use App\Tenant\IdentityContext\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\PlatformContext\ExportImportModule\Models\TenantCsvTransferRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

final class ProcessTenantCsvExportJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public int $tries = 3;
   public int $timeout = 180;

   public function __construct(
      private readonly int $runId,
   ) {
      $this->onQueue('exports');
   }

   public function uniqueId(): string {
      return 'tenant-csv-export-' . $this->runId;
   }

   /**
    * @return array<int, EnsureTenantContext>
    */
   public function middleware(): array {
      return [new EnsureTenantContext];
   }

   public function handle(): void {
      /** @var TenantCsvTransferRun|null $run */
      $run = TenantCsvTransferRun::on('central')->find($this->runId);

      if (! $run instanceof TenantCsvTransferRun || $run->status !== TenantCsvTransferRun::STATUS_PENDING) {
         return;
      }

      DB::connection('central')->transaction(function () use ($run): void {
         $run->forceFill([
            'status' => TenantCsvTransferRun::STATUS_RUNNING,
            'started_at' => now(),
            'error_message' => null,
         ])->save();
      });

      $rows = TenantUser::query()
         ->orderBy('id')
         ->get(['name', 'email', 'created_at']);

      $directory = storage_path('app/private/tenant-csv/' . $run->tenant_id . '/exports');

      if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
         throw new \RuntimeException('No fue posible crear el directorio para export CSV tenant.');
      }

      $filename = 'tenant-users-export-' . $run->id . '.csv';
      $absolutePath = $directory . '/' . $filename;
      $relativePath = 'tenant-csv/' . $run->tenant_id . '/exports/' . $filename;

      $handle = fopen($absolutePath, 'wb');

      if (! is_resource($handle)) {
         throw new \RuntimeException('No fue posible abrir el archivo CSV para exportacion.');
      }

      fputcsv($handle, ['name', 'email', 'created_at']);

      foreach ($rows as $row) {
         fputcsv($handle, [
            (string) $row->name,
            (string) $row->email,
            (string) optional($row->created_at)->toDateTimeString(),
         ]);
      }

      fclose($handle);

      DB::connection('central')->transaction(function () use ($run, $rows, $relativePath): void {
         $run->forceFill([
            'status' => TenantCsvTransferRun::STATUS_COMPLETED,
            'result_disk' => 'local',
            'result_path' => $relativePath,
            'total_rows' => $rows->count(),
            'processed_rows' => $rows->count(),
            'completed_at' => now(),
            'meta' => [
               'restored_tenant_id' => (string) tenant()?->id,
               'columns' => ['name', 'email', 'created_at'],
            ],
         ])->save();
      });
   }

   public function failed(\Throwable $exception): void {
      /** @var TenantCsvTransferRun|null $run */
      $run = TenantCsvTransferRun::on('central')->find($this->runId);

      if (! $run instanceof TenantCsvTransferRun) {
         return;
      }

      DB::connection('central')->transaction(function () use ($run, $exception): void {
         $run->forceFill([
            'status' => TenantCsvTransferRun::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => mb_substr($exception->getMessage(), 0, 2000),
         ])->save();
      });
   }
}
