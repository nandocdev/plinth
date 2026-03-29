<?php

declare(strict_types=1);

namespace App\Tenant\AuthenticationModule\Livewire\Forms;

use Livewire\Form;

final class TenantLoginForm extends Form {
   public string $email = '';

   public string $password = '';

   public bool $remember = false;

   /**
    * @return array<string, list<string>>
    */
   public function rules(): array {
      return [
         'email' => ['required', 'email'],
         'password' => ['required', 'string', 'min:8'],
      ];
   }

   /**
    * @return array<string, string>
    */
   public function messages(): array {
      return [
         'email.required' => 'El email es obligatorio.',
         'email.email' => 'Introduce un email válido.',
         'password.required' => 'La contraseña es obligatoria.',
         'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      ];
   }
}
