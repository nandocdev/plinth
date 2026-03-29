<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class GlobalLogsFilterForm extends Form {
   public ?string $tenantId = null;

   public ?string $level = null;

   public string $search = '';

   public int $perPage = 25;

   /**
    * @return array{tenantId: ?string, level: ?string, search: string, perPage: int}
    */
   public function payload(): array {
      $this->validate([
         'tenantId' => ['nullable', 'string', 'exists:tenants,id'],
         'level' => ['nullable', 'string', Rule::in(['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])],
         'search' => ['nullable', 'string', 'max:250'],
         'perPage' => ['required', 'integer', 'min:10', 'max:100'],
      ]);

      return [
         'tenantId' => $this->tenantId !== '' ? $this->tenantId : null,
         'level' => $this->level !== '' ? $this->level : null,
         'search' => trim($this->search),
         'perPage' => $this->perPage,
      ];
   }
}
