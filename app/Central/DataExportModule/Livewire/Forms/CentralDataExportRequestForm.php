<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Livewire\Forms;

use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class CentralDataExportRequestForm extends Form {
   public string $tenantId = '';

   public bool $includeActivityLog = true;

   /**
    * @return array{tenantId: string, includeActivityLog: bool}
    */
   public function payload(): array {
      $tenantIds = Tenant::query()->pluck('id')->all();

      $this->validate([
         'tenantId' => ['required', 'string', Rule::in($tenantIds)],
         'includeActivityLog' => ['required', 'boolean'],
      ]);

      return [
         'tenantId' => $this->tenantId,
         'includeActivityLog' => $this->includeActivityLog,
      ];
   }

   public function clear(): void {
      $this->reset();
      $this->includeActivityLog = true;
   }
}
