<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\CreateDomainData;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreateDomainAction {
   public function execute(CreateDomainData $data): Domain {
      /** @var Domain $domain */
      $domain = DB::connection('central')->transaction(function () use ($data): Domain {
         /** @var Tenant|null $tenant */
         $tenant = Tenant::query()->find($data->tenantId);

         if (! $tenant instanceof Tenant) {
            throw new RuntimeException('Tenant no encontrado para asociar dominio.');
         }

         if (Domain::query()->where('domain', $data->domain)->exists()) {
            throw new RuntimeException('El dominio ya se encuentra registrado.');
         }

         /** @var Domain $domain */
         $domain = $tenant->domains()->create([
            'domain' => $data->domain,
            'verified_at' => null,
         ]);

         return $domain;
      });

      return $domain;
   }
}
