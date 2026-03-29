<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Livewire\Forms;

use App\Central\AdminAuthorizationModule\Enums\AdminRole;
use Livewire\Form;

final class AssignRoleForm extends Form {
   public int    $adminId  = 0;
   public string $role     = '';

   public function rules(): array {
      return [
         'adminId' => ['required', 'integer', 'min:1'],
         'role'    => ['required', 'string', 'in:' . implode(',', array_column(AdminRole::cases(), 'value'))],
      ];
   }

   public function messages(): array {
      return [
         'adminId.required' => 'Debes seleccionar un administrador.',
         'role.required'    => 'Debes seleccionar un rol.',
         'role.in'          => 'El rol seleccionado no es válido.',
      ];
   }

   public function toData(): array {
      return [
         'admin_id' => $this->adminId,
         'role'     => $this->role,
      ];
   }
}
