<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Jobs;

use App\Central\TenantProvisioningModule\Events\TenantRecoverySnapshotCompleted;
use App\Central\TenantProvisioningModule\Events\TenantRecoverySnapshotFailed;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantRecoverySnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;
use ZipArchive;

final class RunTenantBackupJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public int $tries = 3;
   public int $timeout = 1200;

   public function __construct(
      private readonly int $snapshotId,
   ) {
      $this->onQueue('backups');
   }

   public function uniqueId(): string {
      return 'tenant-backup-snapshot-' . $this->snapshotId;
   }

   public function handle(): void {
      /** @var TenantRecoverySnapshot|null $snapshot */
      $snapshot = TenantRecoverySnapshot::query()->with('tenant')->find($this->snapshotId);

      if (! $snapshot instanceof TenantRecoverySnapshot) {
         return;
      }

      if ($snapshot->status !== TenantRecoverySnapshot::STATUS_PENDING || $snapshot->operation !== TenantRecoverySnapshot::OPERATION_BACKUP) {
         return;
      }

      $snapshot->update([
         'status' => TenantRecoverySnapshot::STATUS_RUNNING,
         'started_at' => now(),
         'error_message' => null,
      ]);

      $paths = $this->buildSnapshot($snapshot);

      $snapshot->update([
         'status' => TenantRecoverySnapshot::STATUS_COMPLETED,
         'database_dump_path' => $paths['database_dump_path'],
         'storage_archive_path' => $paths['storage_archive_path'],
         'manifest_path' => $paths['manifest_path'],
         'completed_at' => now(),
         'meta' => [
            'database_dump_bytes' => $this->fileSize($paths['database_dump_path']),
            'storage_archive_bytes' => $this->fileSize($paths['storage_archive_path']),
         ],
      ]);

      event(new TenantRecoverySnapshotCompleted($snapshot->fresh()));
   }

   public function failed(Throwable $exception): void {
      /** @var TenantRecoverySnapshot|null $snapshot */
      $snapshot = TenantRecoverySnapshot::query()->find($this->snapshotId);

      if (! $snapshot instanceof TenantRecoverySnapshot) {
         return;
      }

      $snapshot->update([
         'status' => TenantRecoverySnapshot::STATUS_FAILED,
         'completed_at' => now(),
         'error_message' => mb_substr($exception->getMessage(), 0, 1000),
      ]);

      event(new TenantRecoverySnapshotFailed($snapshot, $exception->getMessage()));
   }

   /**
    * @return array{database_dump_path: string, storage_archive_path: string, manifest_path: string}
    */
   private function buildSnapshot(TenantRecoverySnapshot $snapshot): array {
      /** @var Tenant|null $tenant */
      $tenant = $snapshot->tenant;

      if (! $tenant instanceof Tenant) {
         throw new RuntimeException('No se encontro tenant para ejecutar backup.');
      }

      $snapshotDirectory = storage_path('app/private/tenant-snapshots/' . $tenant->id . '/' . $snapshot->id);

      if (! is_dir($snapshotDirectory) && ! mkdir($snapshotDirectory, 0755, true) && ! is_dir($snapshotDirectory)) {
         throw new RuntimeException('No se pudo crear el directorio del snapshot.');
      }

      $databaseDumpPath = $snapshotDirectory . '/database.sql';
      $storageArchivePath = $snapshotDirectory . '/storage.zip';
      $manifestPath = $snapshotDirectory . '/manifest.json';

      $dbConfig = $this->tenantDatabaseConfig($tenant);

      $this->dumpPostgreSqlDatabase($dbConfig, $databaseDumpPath);
      $this->archiveTenantStorage($tenant, $storageArchivePath);

      file_put_contents($manifestPath, json_encode([
         'tenant_id' => $tenant->id,
         'snapshot_id' => $snapshot->id,
         'operation' => TenantRecoverySnapshot::OPERATION_BACKUP,
         'generated_at' => now()->toIso8601String(),
         'database_name' => $dbConfig['database'],
      ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

      return [
         'database_dump_path' => $databaseDumpPath,
         'storage_archive_path' => $storageArchivePath,
         'manifest_path' => $manifestPath,
      ];
   }

   /**
    * @return array{driver: string, host: string, port: string, database: string, username: string, password: string}
    */
   private function tenantDatabaseConfig(Tenant $tenant): array {
      $template = (array) config('database.connections.tenant_template', []);
      $internalPrefix = (string) config('tenancy.database.internal_prefix', 'tenancy_');
      $database = (string) ($tenant->getAttribute($internalPrefix . 'db_name')
         ?? ((string) config('tenancy.database.prefix', 'tenant')) . $tenant->id . ((string) config('tenancy.database.suffix', '')));

      return [
         'driver' => (string) ($template['driver'] ?? 'pgsql'),
         'host' => (string) ($template['host'] ?? '127.0.0.1'),
         'port' => (string) ($template['port'] ?? '5432'),
         'database' => $database,
         'username' => (string) ($template['username'] ?? ''),
         'password' => (string) ($template['password'] ?? ''),
      ];
   }

   /**
    * @param array{driver: string, host: string, port: string, database: string, username: string, password: string} $dbConfig
    */
   private function dumpPostgreSqlDatabase(array $dbConfig, string $databaseDumpPath): void {
      if ($dbConfig['driver'] !== 'pgsql') {
         throw new RuntimeException('El backup por tenant solo soporta PostgreSQL en este entorno.');
      }

      $command = [
         'pg_dump',
         '--host=' . $dbConfig['host'],
         '--port=' . $dbConfig['port'],
         '--username=' . $dbConfig['username'],
         '--format=plain',
         '--clean',
         '--if-exists',
         '--no-owner',
         '--no-acl',
         '--file=' . $databaseDumpPath,
         $dbConfig['database'],
      ];

      $environment = $dbConfig['password'] === '' ? [] : ['PGPASSWORD' => $dbConfig['password']];

      $result = Process::env($environment)->timeout(300)->run($command);

      if ($result->failed()) {
         throw new RuntimeException('Fallo pg_dump de tenant: ' . trim($result->errorOutput() . ' ' . $result->output()));
      }
   }

   private function archiveTenantStorage(Tenant $tenant, string $storageArchivePath): void {
      $tenantStoragePath = storage_path('app/tenants/' . $tenant->id);

      $zip = new ZipArchive();

      if ($zip->open($storageArchivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
         throw new RuntimeException('No se pudo crear el archivo zip de storage tenant.');
      }

      if (! is_dir($tenantStoragePath)) {
         $zip->addEmptyDir('tenant-storage');
         $zip->close();

         return;
      }

      $iterator = new \RecursiveIteratorIterator(
         new \RecursiveDirectoryIterator($tenantStoragePath, \FilesystemIterator::SKIP_DOTS),
         \RecursiveIteratorIterator::SELF_FIRST,
      );

      foreach ($iterator as $fileInfo) {
         $absolutePath = $fileInfo->getPathname();
         $relativePath = ltrim(substr($absolutePath, strlen($tenantStoragePath)), '/');

         if ($relativePath === '') {
            continue;
         }

         if ($fileInfo->isDir()) {
            $zip->addEmptyDir($relativePath);

            continue;
         }

         $zip->addFile($absolutePath, $relativePath);
      }

      $zip->close();
   }

   private function fileSize(string $path): int {
      $size = @filesize($path);

      return is_int($size) ? $size : 0;
   }
}
