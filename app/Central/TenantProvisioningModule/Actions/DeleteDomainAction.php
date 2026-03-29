<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\DeleteDomainData;
use App\Central\TenantProvisioningModule\Models\Domain;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DeleteDomainAction {
   public function execute(DeleteDomainData $data): void {
      DB::connection('central')->transaction(function () use ($data): void {
         /** @var Domain $domain */
         $domain = Domain::query()
            ->where('id', $data->domainId)
            ->where('tenant_id', $data->tenantId)
            ->firstOrFail();

         $domainsCount = Domain::query()->where('tenant_id', $data->tenantId)->count();

         if ($domainsCount <= 1) {
            throw new RuntimeException('No se puede eliminar el unico dominio del tenant.');
         }

         $domain->delete();
      });
   }
}
