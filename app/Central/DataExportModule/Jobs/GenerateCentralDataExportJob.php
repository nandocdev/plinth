<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Jobs;

use App\Central\DataExportModule\Actions\BuildCentralDataExportPayloadAction;
use App\Central\DataExportModule\Events\CentralDataExportCompleted;
use App\Central\DataExportModule\Events\CentralDataExportFailed;
use App\Central\DataExportModule\Models\CentralDataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;
use ZipArchive;

final class GenerateCentralDataExportJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public int $tries = 3;
   public int $timeout = 600;

   public function __construct(
      private readonly int $exportId,
   ) {
      $this->onQueue('exports');
   }

   public function uniqueId(): string {
      return 'central-data-export-' . $this->exportId;
   }

   public function handle(BuildCentralDataExportPayloadAction $buildPayload): void {
      /** @var CentralDataExport|null $export */
      $export = CentralDataExport::query()->with(['tenant', 'requestedBy'])->find($this->exportId);

      if (! $export instanceof CentralDataExport) {
         return;
      }

      if ($export->status !== CentralDataExport::STATUS_PENDING) {
         return;
      }

      $export->update([
         'status' => CentralDataExport::STATUS_RUNNING,
         'started_at' => now(),
         'error_message' => null,
      ]);

      $payload = $buildPayload->execute($export);
      $paths = $this->writeExportFiles($export, $payload);

      $export->update([
         'status' => CentralDataExport::STATUS_COMPLETED,
         'file_path' => $paths['archive_path'],
         'checksum_sha256' => hash_file('sha256', $paths['archive_path']) ?: null,
         'size_bytes' => $this->fileSize($paths['archive_path']),
         'completed_at' => now(),
         'meta' => [
            'manifest' => $payload->manifest(),
            'files' => $paths['json_files'],
         ],
      ]);

      event(new CentralDataExportCompleted($export->fresh()));
   }

   public function failed(Throwable $exception): void {
      /** @var CentralDataExport|null $export */
      $export = CentralDataExport::query()->find($this->exportId);

      if (! $export instanceof CentralDataExport) {
         return;
      }

      $export->update([
         'status' => CentralDataExport::STATUS_FAILED,
         'completed_at' => now(),
         'error_message' => mb_substr($exception->getMessage(), 0, 2000),
      ]);

      event(new CentralDataExportFailed($export, $exception->getMessage()));
   }

   /**
    * @return array{archive_path: string, json_files: list<string>}
    */
   private function writeExportFiles(CentralDataExport $export, \App\Central\DataExportModule\DTOs\CentralDataExportPayloadData $payload): array {
      $directory = storage_path('app/private/central-data-exports/' . $export->tenant_id . '/' . $export->id);

      if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
         throw new RuntimeException('No se pudo crear el directorio del export GDPR.');
      }

      $files = [
         'manifest.json' => $payload->manifest(),
         'tenant.json' => $payload->tenant,
         'domains.json' => $payload->domains,
         'billing.json' => $payload->billing,
         'activity-log.json' => $payload->activityLog,
         'recovery-snapshots.json' => $payload->recoverySnapshots,
      ];

      $writtenFiles = [];

      foreach ($files as $filename => $contents) {
         $path = $directory . '/' . $filename;
         $encoded = json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

         if ($encoded === false || file_put_contents($path, $encoded) === false) {
            throw new RuntimeException('No se pudo escribir el archivo ' . $filename . ' del export GDPR.');
         }

         $writtenFiles[] = $path;
      }

      $archivePath = $directory . '/tenant-' . $export->tenant_id . '-gdpr-export.zip';
      $zip = new ZipArchive();

      if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
         throw new RuntimeException('No se pudo crear el archivo ZIP del export GDPR.');
      }

      foreach ($writtenFiles as $filePath) {
         $zip->addFile($filePath, basename($filePath));
      }

      $zip->close();

      return [
         'archive_path' => $archivePath,
         'json_files' => $writtenFiles,
      ];
   }

   private function fileSize(string $path): int {
      $size = @filesize($path);

      return is_int($size) ? $size : 0;
   }
}
