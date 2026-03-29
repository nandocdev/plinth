<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class SystemHealthFilterForm extends Form {
   public string $connectionFilter = 'all';

   public bool $onlyUnhealthy = false;

   /**
    * @return array{connectionFilter: string, onlyUnhealthy: bool}
    */
   public function payload(): array {
      $this->validate([
         'connectionFilter' => ['required', 'string', Rule::in(['all', 'central', 'tenant_template'])],
         'onlyUnhealthy' => ['boolean'],
      ]);

      return [
         'connectionFilter' => $this->connectionFilter,
         'onlyUnhealthy' => $this->onlyUnhealthy,
      ];
   }
}
