<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\Jobs;

use App\Shared\Infrastructure\Jobs\Middleware\EnsureTenantContext;
use App\Tenant\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\ExportImportModule\Models\TenantCsvTransferRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ProcessTenantCsvImportJob implements ShouldQueue, ShouldBeUnique {
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public int $tries = 1;
   public int $timeout = 240;

   public function __construct(
      private readonly int $runId,
   ) {
      $this->onQueue('imports');
   }

   public function uniqueId(): string {
      return 'tenant-csv-import-' . $this->runId;
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

      $disk = (string) $run->source_disk;
      $path = (string) $run->source_path;

      if ($disk === '' || $path === '') {
         throw new \RuntimeException('La importacion CSV no tiene source_disk/source_path validos.');
      }

      if (! Storage::disk($disk)->exists($path)) {
         throw new \RuntimeException('No existe el archivo CSV a importar para el tenant actual.');
      }

      $absolutePath = Storage::disk($disk)->path($path);
      $file = new \SplFileObject($absolutePath);
      $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

      $header = null;
      $processed = 0;

      DB::transaction(function () use ($file, &$header, &$processed): void {
         foreach ($file as $row) {
            if (! is_array($row) || $row === [null]) {
               continue;
            }

            if ($header === null) {
               $header = array_map(static fn($value): string => strtolower(trim((string) $value)), $row);

               continue;
            }

            $record = $this->normalizeCsvRecord($header, $row);

            if ($record === null) {
               continue;
            }

            /** @var TenantUser|null $existing */
            $existing = TenantUser::query()->where('email', $record['email'])->first();

            if ($existing instanceof TenantUser) {
               $existing->forceFill([
                  'name' => $record['name'],
               ])->save();
            } else {
               TenantUser::query()->create([
                  'name' => $record['name'],
                  'email' => $record['email'],
                  'password' => Hash::make(Str::random(24)),
               ]);
            }

            $processed++;
         }
      }, 3);

      DB::connection('central')->transaction(function () use ($run, $processed): void {
         $run->forceFill([
            'status' => TenantCsvTransferRun::STATUS_COMPLETED,
            'total_rows' => $processed,
            'processed_rows' => $processed,
            'completed_at' => now(),
            'meta' => [
               'restored_tenant_id' => (string) tenant()?->id,
               'source_disk' => $run->source_disk,
               'source_path' => $run->source_path,
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

   /**
    * @param  array<int, string>  $header
    * @param  array<int, mixed>  $row
    * @return array{name: string, email: string}|null
    */
   private function normalizeCsvRecord(array $header, array $row): ?array {
      $columns = [];

      foreach ($header as $index => $column) {
         $columns[$column] = trim((string) ($row[$index] ?? ''));
      }

      $email = mb_strtolower((string) ($columns['email'] ?? ''));

      if ($email === '') {
         return null;
      }

      $name = (string) ($columns['name'] ?? '');
      $name = $name !== '' ? $name : 'Imported User';

      return [
         'name' => $name,
         'email' => $email,
      ];
   }
}
