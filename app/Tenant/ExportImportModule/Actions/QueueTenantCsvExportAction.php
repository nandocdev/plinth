<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\Actions;

use App\Tenant\ExportImportModule\DTOs\QueueTenantCsvExportData;
use App\Tenant\ExportImportModule\Jobs\ProcessTenantCsvExportJob;
use App\Tenant\ExportImportModule\Models\TenantCsvTransferRun;
use Illuminate\Support\Facades\DB;

final class QueueTenantCsvExportAction {
   public function execute(QueueTenantCsvExportData $data): TenantCsvTransferRun {
      /** @var TenantCsvTransferRun $run */
      $run = DB::connection('central')->transaction(function () use ($data): TenantCsvTransferRun {
         /** @var TenantCsvTransferRun $created */
         $created = TenantCsvTransferRun::on('central')->create([
            'tenant_id' => $data->tenantId,
            'requested_by_user_id' => $data->requestedByUserId,
            'type' => TenantCsvTransferRun::TYPE_EXPORT,
            'status' => TenantCsvTransferRun::STATUS_PENDING,
            'meta' => [],
         ]);

         return $created;
      }, 3);

      ProcessTenantCsvExportJob::dispatch($run->id)->onQueue('exports');

      return $run;
   }
}
