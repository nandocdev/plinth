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

final class RunTenantRestoreJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public int $tries = 3;
   public int $timeout = 1200;

   public function __construct(
      private readonly int $snapshotId,
   ) {
      $this->onQueue('backups');
   }

   public function uniqueId(): string {
      return 'tenant-restore-snapshot-' . $this->snapshotId;
   }

   public function handle(): void {
      /** @var TenantRecoverySnapshot|null $snapshot */
      $snapshot = TenantRecoverySnapshot::query()->with(['tenant', 'sourceSnapshot'])->find($this->snapshotId);

      if (! $snapshot instanceof TenantRecoverySnapshot) {
         return;
      }

      if ($snapshot->status !== TenantRecoverySnapshot::STATUS_PENDING || $snapshot->operation !== TenantRecoverySnapshot::OPERATION_RESTORE) {
         return;
      }

      $snapshot->update([
         'status' => TenantRecoverySnapshot::STATUS_RUNNING,
         'started_at' => now(),
         'error_message' => null,
      ]);

      $this->restoreFromSourceSnapshot($snapshot);

      $snapshot->update([
         'status' => TenantRecoverySnapshot::STATUS_COMPLETED,
         'completed_at' => now(),
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

   private function restoreFromSourceSnapshot(TenantRecoverySnapshot $snapshot): void {
      /** @var Tenant|null $tenant */
      $tenant = $snapshot->tenant;

      if (! $tenant instanceof Tenant) {
         throw new RuntimeException('No se encontro tenant para ejecutar restore.');
      }

      /** @var TenantRecoverySnapshot|null $source */
      $source = $snapshot->sourceSnapshot;

      if (! $source instanceof TenantRecoverySnapshot) {
         throw new RuntimeException('No se encontro snapshot fuente para restore.');
      }

      if ($source->operation !== TenantRecoverySnapshot::OPERATION_BACKUP || $source->status !== TenantRecoverySnapshot::STATUS_COMPLETED) {
         throw new RuntimeException('El snapshot fuente no esta disponible para restore.');
      }

      if (! is_file((string) $source->database_dump_path) || ! is_file((string) $source->storage_archive_path)) {
         throw new RuntimeException('El snapshot fuente no contiene artefactos validos de base de datos y storage.');
      }

      $dbConfig = $this->tenantDatabaseConfig($tenant);

      $this->restorePostgreSqlDatabase($dbConfig, (string) $source->database_dump_path);
      $this->restoreTenantStorage($tenant, (string) $source->storage_archive_path);
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
   private function restorePostgreSqlDatabase(array $dbConfig, string $databaseDumpPath): void {
      if ($dbConfig['driver'] !== 'pgsql') {
         throw new RuntimeException('El restore por tenant solo soporta PostgreSQL en este entorno.');
      }

      $command = [
         'psql',
         '--host=' . $dbConfig['host'],
         '--port=' . $dbConfig['port'],
         '--username=' . $dbConfig['username'],
         '--dbname=' . $dbConfig['database'],
         '--set=ON_ERROR_STOP=1',
         '--single-transaction',
         '--file=' . $databaseDumpPath,
      ];

      $environment = $dbConfig['password'] === '' ? [] : ['PGPASSWORD' => $dbConfig['password']];

      $result = Process::env($environment)->timeout(300)->run($command);

      if ($result->failed()) {
         throw new RuntimeException('Fallo restore de base de datos tenant: ' . trim($result->errorOutput() . ' ' . $result->output()));
      }
   }

   private function restoreTenantStorage(Tenant $tenant, string $storageArchivePath): void {
      $tenantStoragePath = storage_path('app/tenants/' . $tenant->id);

      if (is_dir($tenantStoragePath)) {
         $this->deleteDirectoryContents($tenantStoragePath);
      }

      if (! is_dir($tenantStoragePath) && ! mkdir($tenantStoragePath, 0755, true) && ! is_dir($tenantStoragePath)) {
         throw new RuntimeException('No se pudo preparar el directorio de storage tenant para restore.');
      }

      $zip = new ZipArchive();

      if ($zip->open($storageArchivePath) !== true) {
         throw new RuntimeException('No se pudo abrir el archivo zip de storage tenant.');
      }

      $zip->extractTo($tenantStoragePath);
      $zip->close();
   }

   private function deleteDirectoryContents(string $directory): void {
      $items = scandir($directory);

      if (! is_array($items)) {
         return;
      }

      foreach ($items as $item) {
         if ($item === '.' || $item === '..') {
            continue;
         }

         $path = $directory . DIRECTORY_SEPARATOR . $item;

         if (is_dir($path)) {
            $this->deleteDirectoryContents($path);
            rmdir($path);

            continue;
         }

         unlink($path);
      }
   }
}
