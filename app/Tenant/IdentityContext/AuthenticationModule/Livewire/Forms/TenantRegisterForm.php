<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthenticationModule\Livewire\Forms;

use Livewire\Form;

final class TenantRegisterForm extends Form {
   public string $name = '';

   public string $email = '';

   public string $password = '';

   public string $passwordConfirmation = '';

   /**
    * @return array<string, list<string>>
    */
   public function rules(): array {
      return [
         'name' => ['required', 'string', 'min:3', 'max:120'],
         'email' => ['required', 'email', 'max:255', 'unique:users,email'],
         'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
         'passwordConfirmation' => ['required', 'string', 'min:8'],
      ];
   }

   /**
    * @return array<string, string>
    */
   public function messages(): array {
      return [
         'name.required' => 'El nombre es obligatorio.',
         'name.min' => 'El nombre debe tener al menos 3 caracteres.',
         'email.required' => 'El email es obligatorio.',
         'email.email' => 'Introduce un email válido.',
         'email.unique' => 'Ya existe una cuenta con ese email en este tenant.',
         'password.required' => 'La contraseña es obligatoria.',
         'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
         'password.same' => 'La confirmación de contraseña no coincide.',
         'passwordConfirmation.required' => 'La confirmación de contraseña es obligatoria.',
      ];
   }
}
