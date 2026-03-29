<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class GlobalLogsFilterForm extends Form {
   public ?string $tenantId = null;

   public ?string $event = null;

   public ?int $causerId = null;

   public string $search = '';

   public int $perPage = 25;

   /**
    * @return array{tenantId: ?string, event: ?string, causerId: ?int, search: string, perPage: int}
    */
   public function payload(): array {
      $this->validate([
         'tenantId' => ['nullable', 'string', 'exists:tenants,id'],
         'event' => ['nullable', 'string', Rule::in(['get.request', 'post.request', 'put.request', 'patch.request', 'delete.request', 'livewire.action'])],
         'causerId' => ['nullable', 'integer', 'exists:users,id'],
         'search' => ['nullable', 'string', 'max:250'],
         'perPage' => ['required', 'integer', 'min:10', 'max:100'],
      ]);

      return [
         'tenantId' => $this->tenantId !== '' ? $this->tenantId : null,
         'event' => $this->event !== '' ? $this->event : null,
         'causerId' => $this->causerId,
         'search' => trim($this->search),
         'perPage' => $this->perPage,
      ];
   }
}
