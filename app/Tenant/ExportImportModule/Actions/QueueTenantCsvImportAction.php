<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\Actions;

use App\Tenant\ExportImportModule\DTOs\QueueTenantCsvImportData;
use App\Tenant\ExportImportModule\Jobs\ProcessTenantCsvImportJob;
use App\Tenant\ExportImportModule\Models\TenantCsvTransferRun;
use Illuminate\Support\Facades\DB;

final class QueueTenantCsvImportAction {
   public function execute(QueueTenantCsvImportData $data): TenantCsvTransferRun {
      /** @var TenantCsvTransferRun $run */
      $run = DB::connection('central')->transaction(function () use ($data): TenantCsvTransferRun {
         /** @var TenantCsvTransferRun $created */
         $created = TenantCsvTransferRun::on('central')->create([
            'tenant_id' => $data->tenantId,
            'requested_by_user_id' => $data->requestedByUserId,
            'type' => TenantCsvTransferRun::TYPE_IMPORT,
            'status' => TenantCsvTransferRun::STATUS_PENDING,
            'source_disk' => $data->sourceDisk,
            'source_path' => $data->sourcePath,
            'meta' => [],
         ]);

         return $created;
      }, 3);

      ProcessTenantCsvImportJob::dispatch($run->id)->onQueue('imports');

      return $run;
   }
}
