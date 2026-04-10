<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Livewire\Forms;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class UpdateProfileForm extends Form {
   public string $name    = '';
   public string $email   = '';
   public int    $userId  = 0;

   public function fillFromUser(User $user): void {
      $this->userId = $user->id;
      $this->name   = $user->name;
      $this->email  = $user->email;
   }

   /**
    * @return array<string, list<string|object>>
    */
   public function rules(): array {
      return [
         'name'  => ['required', 'string', 'max:100'],
         'email' => [
            'required',
            'email',
            'max:150',
            Rule::unique('users', 'email')->ignore($this->userId),
         ],
      ];
   }

   /**
    * @return array<string, string>
    */
   public function messages(): array {
      return [
         'name.required'  => 'El nombre es obligatorio.',
         'name.max'       => 'El nombre no puede exceder 100 caracteres.',
         'email.required' => 'El correo electrónico es obligatorio.',
         'email.email'    => 'Ingresa un correo electrónico válido.',
         'email.unique'   => 'Este correo ya está en uso por otro usuario.',
      ];
   }
}
