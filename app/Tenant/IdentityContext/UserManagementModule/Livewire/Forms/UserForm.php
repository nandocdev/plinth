<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\UserManagementModule\Livewire\Forms;

use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole;
use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantUserStatus;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class UserForm extends Form {
   #[Validate('required|string|max:100')]
   public string $name = '';

   #[Validate('required|email|max:255')]
   public string $email = '';

   #[Validate('nullable|string|min:8|max:100')]
   public ?string $password = null;

   #[Validate('required|string')]
   public string $role = TenantRole::Member->value;

   #[Validate('required|string')]
   public string $status = TenantUserStatus::Active->value;

   /** @return array<string, string> */
   public function rules(): array {
      $emailUnique = $this->editingId
         ? "required|email|max:255|unique:users,email,{$this->editingId}"
         : 'required|email|max:255|unique:users,email';

      return [
         'name'     => ['required', 'string', 'max:100'],
         'email'    => [$emailUnique],
         'password' => $this->editingId ? ['nullable', 'string', 'min:8', 'max:100'] : ['required', 'string', 'min:8', 'max:100'],
         'role'     => ['required', 'string', 'in:' . implode(',', TenantRole::values())],
         'status'   => ['required', 'string', 'in:active,inactive'],
      ];
   }

   // ID del usuario en edición (null = creación)
   public ?int $editingId = null;
}
