<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Actions;

use App\Central\DataExportModule\DTOs\RequestCentralDataExportData;
use App\Central\DataExportModule\Jobs\GenerateCentralDataExportJob;
use App\Central\DataExportModule\Models\CentralDataExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class QueueCentralDataExportAction {
   public function execute(RequestCentralDataExportData $data): CentralDataExport {
      /** @var CentralDataExport $export */
      $export = DB::connection('central')->transaction(function () use ($data): CentralDataExport {
         $existing = CentralDataExport::query()
            ->where('tenant_id', $data->tenantId)
            ->whereIn('status', [
               CentralDataExport::STATUS_PENDING,
               CentralDataExport::STATUS_RUNNING,
            ])
            ->first();

         if ($existing instanceof CentralDataExport) {
            throw new RuntimeException('Ya existe una exportacion GDPR en progreso para este tenant.');
         }

         /** @var CentralDataExport $created */
         $created = CentralDataExport::query()->create([
            'tenant_id' => $data->tenantId,
            'requested_by_user_id' => $data->requestedByUserId,
            'export_uuid' => (string) Str::uuid(),
            'format' => 'zip',
            'status' => CentralDataExport::STATUS_PENDING,
            'include_activity_log' => $data->includeActivityLog,
            'meta' => [],
         ]);

         return $created;
      }, 3);

      GenerateCentralDataExportJob::dispatch($export->id)->onQueue('exports');

      return $export;
   }
}