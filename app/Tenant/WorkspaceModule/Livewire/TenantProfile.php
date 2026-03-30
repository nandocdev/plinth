<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Livewire;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\WorkspaceModule\Actions\UpdatePasswordAction;
use App\Tenant\WorkspaceModule\Actions\UpdateProfileAction;
use App\Tenant\WorkspaceModule\DTOs\UpdatePasswordData;
use App\Tenant\WorkspaceModule\DTOs\UpdateProfileData;
use App\Tenant\WorkspaceModule\Livewire\Forms\UpdatePasswordForm;
use App\Tenant\WorkspaceModule\Livewire\Forms\UpdateProfileForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Mi Perfil')]
final class TenantProfile extends Component {
   public UpdateProfileForm  $updateProfileForm;
   public UpdatePasswordForm $updatePasswordForm;

   public User $currentUser;

   public ?string $profileSuccess  = null;
   public ?string $passwordSuccess = null;

   public function mount(): void {
      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      if (! $user instanceof User) {
         $this->redirect('/login', navigate: true);

         return;
      }

      $this->authorize('update', $user);

      $this->currentUser = $user;
      $this->updateProfileForm->fillFromUser($user);
   }

   public function updateProfile(UpdateProfileAction $action): void {
      $this->profileSuccess = null;
      $this->updateProfileForm->validate();

      $this->authorize('update', $this->currentUser);

      $this->currentUser = $action->execute(
         $this->currentUser,
         new UpdateProfileData(
            name: $this->updateProfileForm->name,
            email: $this->updateProfileForm->email,
         ),
      );

      // Sincronizar el form con el modelo actualizado
      $this->updateProfileForm->fillFromUser($this->currentUser);

      $this->profileSuccess = __('Perfil actualizado correctamente.');
   }

   public function updatePassword(UpdatePasswordAction $action): void {
      $this->passwordSuccess = null;
      $this->updatePasswordForm->validate();

      $this->authorize('update', $this->currentUser);

      $action->execute(
         $this->currentUser,
         new UpdatePasswordData(
            currentPassword: $this->updatePasswordForm->current_password,
            password: $this->updatePasswordForm->password,
         ),
      );

      $this->updatePasswordForm->reset();

      $this->passwordSuccess = __('Contraseña actualizada correctamente.');
   }

   public function render(): View {
      return view('workspace::livewire.tenant-profile');
   }
}
