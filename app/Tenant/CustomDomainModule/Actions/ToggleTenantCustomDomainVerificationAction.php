<?php

declare(strict_types=1);

namespace App\Tenant\CustomDomainModule\Actions;

use App\Central\TenantProvisioningModule\Models\Domain;
use Illuminate\Support\Facades\DB;

final class ToggleTenantCustomDomainVerificationAction {
   public function execute(string $tenantId, int $domainId): Domain {
      /** @var Domain $domain */
      $domain = DB::connection('central')->transaction(function () use ($tenantId, $domainId): Domain {
         /** @var Domain $domain */
         $domain = Domain::on('central')
            ->where('tenant_id', $tenantId)
            ->where('id', $domainId)
            ->firstOrFail();

         $verifiedAt = $domain->verified_at === null ? now() : null;

         $domain->update([
            'verified_at' => $verifiedAt,
            'ssl_status' => $verifiedAt === null ? 'not_requested' : $domain->ssl_status,
         ]);

         return $domain;
      });

      return $domain;
   }
}
