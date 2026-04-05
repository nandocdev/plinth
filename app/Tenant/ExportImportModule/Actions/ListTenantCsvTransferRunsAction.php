<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\Actions;

use App\Tenant\ExportImportModule\Models\TenantCsvTransferRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTenantCsvTransferRunsAction {
   public function execute(int $perPage, int $page): LengthAwarePaginator {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para listar transferencias CSV.');
      }

      return TenantCsvTransferRun::on('central')
         ->where('tenant_id', $tenantId)
         ->orderByDesc('id')
         ->paginate($perPage, ['*'], 'page', $page);
   }
}
