<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\VerifyDomainData;
use App\Central\TenantProvisioningModule\Models\Domain;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class VerifyDomainAction {
   public function execute(VerifyDomainData $data): Domain {
      /** @var Domain $domain */
      $domain = DB::connection('central')->transaction(function () use ($data): Domain {
         /** @var Domain $domain */
         $domain = Domain::query()
            ->where('id', $data->domainId)
            ->where('tenant_id', $data->tenantId)
            ->firstOrFail();

         $domain->setAttribute(
            'verified_at',
            $data->verified ? CarbonImmutable::now()->toDateTimeString() : null,
         );

         $domain->save();

         return $domain;
      });

      return $domain;
   }
}
