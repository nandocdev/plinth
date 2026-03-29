<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Livewire;

use App\Central\AdminAuthorizationModule\Actions\AssignAdminRoleAction;
use App\Central\AdminAuthorizationModule\Actions\ListAdminsAction;
use App\Central\AdminAuthorizationModule\Actions\RevokeAdminRoleAction;
use App\Central\AdminAuthorizationModule\DTOs\AssignAdminRoleData;
use App\Central\AdminAuthorizationModule\Enums\AdminRole;
use App\Central\AdminAuthorizationModule\Livewire\Forms\AssignRoleForm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Roles de Administradores')]
final class AdminRoleCrud extends Component {
   use WithPagination;

   public AssignRoleForm $form;

   public string $search       = '';
   public bool   $showAssignModal = false;
   public ?int   $selectedAdminId = null;

   public function mount(): void {
      $this->authorize('admin-roles.viewAny');
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function openAssignModal(int $adminId): void {
      $this->authorize('admin-roles.assign');
      $this->selectedAdminId  = $adminId;
      $this->form->adminId    = $adminId;
      $this->form->role       = '';
      $this->showAssignModal  = true;
   }

   public function assignRole(AssignAdminRoleAction $action): void {
      $this->authorize('admin-roles.assign');
      $this->form->validate();

      $action->execute(AssignAdminRoleData::fromArray($this->form->toData()));

      $this->showAssignModal = false;
      $this->form->reset();
      session()->flash('status', 'Rol asignado correctamente.');
   }

   public function revokeRole(RevokeAdminRoleAction $action, int $adminId): void {
      $this->authorize('admin-roles.revoke');

      $action->execute($adminId);

      session()->flash('status', 'Rol revocado.');
   }

   public function render(ListAdminsAction $listAdmins): View {
      return view('admin-authorization::livewire.admin-role-crud', [
         'admins'    => $listAdmins->execute($this->search ?: null),
         'allRoles'  => AdminRole::cases(),
      ]);
   }
}
