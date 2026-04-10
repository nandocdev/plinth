<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\CustomDomainModule\Actions;

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Tenant\GovernanceContext\CustomDomainModule\Jobs\IssueTenantDomainSslCertificateJob;
use Illuminate\Support\Facades\DB;

final class RequestTenantDomainSslCertificateAction {
   public function execute(string $tenantId, int $domainId): Domain {
      /** @var Domain $domain */
      $domain = DB::connection('central')->transaction(function () use ($tenantId, $domainId): Domain {
         /** @var Domain $domain */
         $domain = Domain::on('central')
            ->where('tenant_id', $tenantId)
            ->where('id', $domainId)
            ->firstOrFail();

         $domain->update([
            'ssl_status' => 'requested',
            'ssl_requested_at' => now(),
            'ssl_last_error' => null,
         ]);

         IssueTenantDomainSslCertificateJob::dispatch($tenantId, $domain->id)->onQueue('provisioning');

         return $domain;
      });

      return $domain;
   }
}
