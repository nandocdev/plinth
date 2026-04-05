<?php

declare(strict_types=1);

namespace App\Tenant\CustomDomainModule\Actions;

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Tenant\CustomDomainModule\DTOs\CreateCustomDomainData;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreateTenantCustomDomainAction {
   public function execute(CreateCustomDomainData $data): Domain {
      /** @var Domain $domain */
      $domain = DB::connection('central')->transaction(function () use ($data): Domain {
         if (Domain::query()->on('central')->where('domain', $data->domain)->exists()) {
            throw new RuntimeException('El dominio ya está registrado.');
         }

         /** @var Domain $domain */
         $domain = Domain::query()->on('central')->create([
            'tenant_id' => $data->tenantId,
            'domain' => strtolower(trim($data->domain)),
            'verified_at' => null,
            'ssl_status' => 'not_requested',
            'ssl_requested_at' => null,
            'ssl_issued_at' => null,
            'ssl_expires_at' => null,
            'ssl_last_error' => null,
         ]);

         return $domain;
      });

      return $domain;
   }
}
