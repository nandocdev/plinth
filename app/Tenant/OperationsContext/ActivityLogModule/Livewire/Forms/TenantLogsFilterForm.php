<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\ActivityLogModule\Livewire\Forms;

use Livewire\Form;

final class TenantLogsFilterForm extends Form {
   public string $event = '';
   public string $search = '';
   public int $perPage = 20;

   /**
    * @return array{event: ?string, search: string, perPage: int}
    */
   public function payload(): array {
      $event = trim($this->event);
      $search = trim($this->search);

      return [
         'event' => $event !== '' ? $event : null,
         'search' => $search,
         'perPage' => in_array($this->perPage, [20, 50, 100], true) ? $this->perPage : 20,
      ];
   }
}
