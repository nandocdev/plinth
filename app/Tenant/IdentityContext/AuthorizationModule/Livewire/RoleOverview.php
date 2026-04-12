<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthorizationModule\Livewire;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\IdentityContext\AuthorizationModule\Actions\AssignRoleToUserAction;
use App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.tenant')]
#[Title('Roles & Permisos')]
final class RoleOverview extends Component {

   public ?int   $reassigningUserId = null;
   public string $reassignRole      = '';

   public function mount(): void {
      $this->authorize('viewAny', Role::class);
   }

   /** Abre el selector de rol para un usuario concreto. */
   public function startReassign(int $userId, string $currentRole): void {
      $this->authorize('manage', Role::class);

      $this->reassigningUserId = $userId;
      $this->reassignRole      = $currentRole;
   }

   public function cancelReassign(): void {
      $this->reassigningUserId = null;
      $this->reassignRole      = '';
   }

   public function confirmReassign(AssignRoleToUserAction $action): void {
      $this->authorize('manage', Role::class);

      $this->validate([
         'reassigningUserId' => ['required', 'integer', 'exists:users,id'],
         'reassignRole'      => ['required', 'string', 'in:' . implode(',', TenantRole::values())],
      ]);

      /** @var User $target */
      $target = User::query()->findOrFail($this->reassigningUserId);

      $action->execute($target, TenantRole::from($this->reassignRole));

      $this->cancelReassign();
   }

   public function render(): View {
      /** @var Collection<int, Role> $roles */
      $roles = Role::withCount('users')
         ->with(['users' => fn($q) => $q
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name'),
         ])
         ->orderByRaw("CASE name WHEN 'admin' THEN 0 WHEN 'manager' THEN 1 ELSE 2 END")
         ->get();

      return view('authorization::livewire.role-overview', [
         'roles'      => $roles,
         'tenantRole' => TenantRole::class,
         'canManage'  => auth('tenant')->user()?->can('manage', Role::class) ?? false,
      ]);
   }
}
