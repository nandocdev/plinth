<?php

declare(strict_types=1);

namespace App\Tenant\UserManagementModule\Livewire;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\UserManagementModule\Actions\CreateTenantUserAction;
use App\Tenant\UserManagementModule\Actions\DeleteTenantUserAction;
use App\Tenant\UserManagementModule\Actions\UpdateTenantUserAction;
use App\Tenant\UserManagementModule\DTOs\CreateTenantUserData;
use App\Tenant\UserManagementModule\DTOs\UpdateTenantUserData;
use App\Tenant\UserManagementModule\Enums\TenantRole;
use App\Tenant\UserManagementModule\Enums\TenantUserStatus;
use App\Tenant\UserManagementModule\Livewire\Forms\UserForm;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Gestión de Usuarios')]
final class UserList extends Component {
   use WithPagination;

   public UserForm $form;

   public string  $search       = '';
   public bool    $showModal    = false;
   public ?int    $deletingId   = null;

   public function mount(): void {
      $this->authorize('viewAny', User::class);
   }

   public function openCreateModal(): void {
      $this->authorize('create', User::class);
      $this->form->reset();
      $this->form->editingId = null;
      $this->showModal = true;
   }

   public function openEditModal(int $userId): void {
      /** @var User $user */
      $user = User::query()->findOrFail($userId);
      $this->authorize('update', $user);

      $this->form->editingId = $user->id;
      $this->form->name      = $user->name;
      $this->form->email     = $user->email;
      $this->form->password  = null;
      $this->form->role      = $user->getRoleNames()->first() ?? TenantRole::Member->value;
      $this->form->status    = $user->status instanceof TenantUserStatus
         ? $user->status->value
         : (string) $user->status;

      $this->showModal = true;
   }

   public function save(
      CreateTenantUserAction $createAction,
      UpdateTenantUserAction $updateAction,
   ): void {
      $validated = $this->form->validate($this->form->rules());

      if ($this->form->editingId) {
         /** @var User $user */
         $user = User::query()->findOrFail($this->form->editingId);
         $this->authorize('update', $user);

         $updateAction->execute($user, UpdateTenantUserData::fromArray($validated));
      } else {
         $this->authorize('create', User::class);
         $createAction->execute(CreateTenantUserData::fromArray($validated));
      }

      $this->showModal = false;
      $this->form->reset();
      $this->resetPage();
   }

   public function confirmDelete(int $userId): void {
      /** @var User $user */
      $user = User::query()->findOrFail($userId);
      $this->authorize('delete', $user);

      $this->deletingId = $userId;
   }

   public function destroyConfirmed(DeleteTenantUserAction $action): void {
      if (! $this->deletingId) {
         return;
      }

      /** @var User $target */
      $target = User::query()->findOrFail($this->deletingId);
      $this->authorize('delete', $target);

      /** @var User $actor */
      $actor = Auth::guard('tenant')->user();

      $action->execute($target, $actor);

      $this->deletingId = null;
      $this->resetPage();
   }

   public function cancelDelete(): void {
      $this->deletingId = null;
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function render(): View {
      /** @var LengthAwarePaginator $users */
      $users = User::query()
         ->with('roles')
         ->when(
            $this->search,
            fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
               ->orWhere('email', 'ilike', "%{$this->search}%"),
         )
         ->orderBy('name')
         ->paginate(15);

      return view('user-management::livewire.user-list', [
         'users'    => $users,
         'roles'    => TenantRole::options(),
         'statuses' => array_column(
            array_map(fn(TenantUserStatus $s) => ['value' => $s->value, 'label' => $s->label()], TenantUserStatus::cases()),
            'label',
            'value',
         ),
      ]);
   }
}
