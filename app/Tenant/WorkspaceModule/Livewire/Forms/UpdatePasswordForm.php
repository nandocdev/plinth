<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Livewire\Forms;

use Livewire\Form;

final class UpdatePasswordForm extends Form {
   public string $current_password = '';
   public string $password         = '';
   public string $password_confirmation = '';

   /**
    * @return array<string, list<string|object>>
    */
   public function rules(): array {
      return [
         'current_password'      => ['required', 'string'],
         'password'              => ['required', 'string', 'min:8', 'confirmed'],
         'password_confirmation' => ['required', 'string'],
      ];
   }

   /**
    * @return array<string, string>
    */
   public function messages(): array {
      return [
         'current_password.required' => 'La contraseña actual es obligatoria.',
         'password.required'         => 'La nueva contraseña es obligatoria.',
         'password.min'              => 'La contraseña debe tener al menos 8 caracteres.',
         'password.confirmed'        => 'La confirmación de contraseña no coincide.',
         'password_confirmation.required' => 'Confirma la nueva contraseña.',
      ];
   }

   public function reset(...$properties): void {
      parent::reset(...$properties);
      $this->current_password      = '';
      $this->password              = '';
      $this->password_confirmation = '';
   }
}
