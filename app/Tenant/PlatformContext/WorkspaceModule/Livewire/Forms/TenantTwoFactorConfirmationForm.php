<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Livewire\Forms;

use Livewire\Form;

final class TenantTwoFactorConfirmationForm extends Form {
   public string $code = '';

   /**
    * @return array<string, list<string>>
    */
   public function rules(): array {
      return [
         'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
      ];
   }

   public function resetCode(): void {
      $this->code = '';
   }
}
