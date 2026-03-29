<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\Actions;

use App\Central\SystemHealthModule\DTOs\ConnectionHealthData;
use App\Central\SystemHealthModule\DTOs\QueueHealthData;
use App\Central\SystemHealthModule\DTOs\StorageHealthData;
use App\Central\SystemHealthModule\DTOs\SystemHealthSnapshotData;
use Illuminate\Support\Facades\DB;
use Throwable;

final class BuildSystemHealthSnapshotAction {
   public function execute(): SystemHealthSnapshotData {
      return new SystemHealthSnapshotData(
         connections: $this->checkConnections(),
         queue: $this->buildQueueHealth(),
         storage: $this->buildStorageHealth(),
         generatedAt: now()->toDateTimeString(),
      );
   }

   /**
    * @return list<ConnectionHealthData>
    */
   private function checkConnections(): array {
      $connections = ['central', 'tenant_template'];
      $results = [];

      foreach ($connections as $name) {
         try {
            DB::connection($name)->select('select 1');

            $results[] = new ConnectionHealthData(
               name: $name,
               ok: true,
               status: 'ok',
               error: null,
            );
         } catch (Throwable $e) {
            $results[] = new ConnectionHealthData(
               name: $name,
               ok: false,
               status: 'error',
               error: $e->getMessage(),
            );
         }
      }

      return $results;
   }

   private function buildQueueHealth(): QueueHealthData {
      $connection = (string) config('queue.default', 'sync');

      $pendingJobs = 0;
      $failedJobs = 0;

      try {
         $pendingJobs = DB::connection('central')->table('jobs')->count();
      } catch (Throwable) {
         $pendingJobs = 0;
      }

      try {
         $failedJobs = DB::connection('central')->table('failed_jobs')->count();
      } catch (Throwable) {
         $failedJobs = 0;
      }

      $healthy = $failedJobs === 0;

      return new QueueHealthData(
         connection: $connection,
         pendingJobs: $pendingJobs,
         failedJobs: $failedJobs,
         healthy: $healthy,
      );
   }

   private function buildStorageHealth(): StorageHealthData {
      $appBytes = $this->directorySize(storage_path('app'));
      $logsBytes = $this->directorySize(storage_path('logs'));
      $frameworkBytes = $this->directorySize(storage_path('framework'));
      $total = $appBytes + $logsBytes + $frameworkBytes;

      return new StorageHealthData(
         appBytes: $appBytes,
         logsBytes: $logsBytes,
         frameworkBytes: $frameworkBytes,
         totalBytes: $total,
         formattedTotal: $this->formatBytes($total),
      );
   }

   private function directorySize(string $path): int {
      if (! is_dir($path)) {
         return 0;
      }

      $size = 0;

      $iterator = new \RecursiveIteratorIterator(
         new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
      );

      foreach ($iterator as $file) {
         if ($file->isFile()) {
            $size += $file->getSize();
         }
      }

      return $size;
   }

   private function formatBytes(int $bytes): string {
      if ($bytes < 1024) {
         return $bytes . ' B';
      }

      $units = ['KB', 'MB', 'GB', 'TB'];
      $value = $bytes / 1024;

      foreach ($units as $unit) {
         if ($value < 1024) {
            return number_format($value, 2) . ' ' . $unit;
         }

         $value /= 1024;
      }

      return number_format($value, 2) . ' PB';
   }
}
