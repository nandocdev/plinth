<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Livewire;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\PlatformContext\WorkspaceModule\Actions\ConfirmTenantTwoFactorAction;
use App\Tenant\PlatformContext\WorkspaceModule\Actions\DisableTenantTwoFactorAction;
use App\Tenant\PlatformContext\WorkspaceModule\Actions\EnableTenantTwoFactorAction;
use App\Tenant\PlatformContext\WorkspaceModule\Actions\RegenerateTenantTwoFactorRecoveryCodesAction;
use App\Tenant\PlatformContext\WorkspaceModule\Actions\UpdatePasswordAction;
use App\Tenant\PlatformContext\WorkspaceModule\Actions\UpdateProfileAction;
use App\Tenant\PlatformContext\WorkspaceModule\DTOs\UpdatePasswordData;
use App\Tenant\PlatformContext\WorkspaceModule\DTOs\UpdateProfileData;
use App\Tenant\PlatformContext\WorkspaceModule\Livewire\Forms\TenantTwoFactorConfirmationForm;
use App\Tenant\PlatformContext\WorkspaceModule\Livewire\Forms\UpdatePasswordForm;
use App\Tenant\PlatformContext\WorkspaceModule\Livewire\Forms\UpdateProfileForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Mi Perfil')]
final class TenantProfile extends Component {
   public UpdateProfileForm  $updateProfileForm;
   public UpdatePasswordForm $updatePasswordForm;
   public TenantTwoFactorConfirmationForm $twoFactorForm;

   public User $currentUser;

   public ?string $profileSuccess  = null;
   public ?string $passwordSuccess = null;
   public ?string $twoFactorSuccess = null;

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

   public function enableTwoFactor(EnableTenantTwoFactorAction $action): void {
      $this->twoFactorSuccess = null;
      $this->authorize('update', $this->currentUser);

      $this->currentUser = $action->execute($this->currentUser);
      $this->twoFactorForm->resetCode();
      $this->twoFactorSuccess = __('2FA iniciado. Escanea el QR y confirma con tu código.');
   }

   public function confirmTwoFactor(ConfirmTenantTwoFactorAction $action): void {
      $this->twoFactorSuccess = null;
      $this->authorize('update', $this->currentUser);
      $this->twoFactorForm->validate();

      try {
         $this->currentUser = $action->execute($this->currentUser, $this->twoFactorForm->code);
         $this->twoFactorForm->resetCode();
         $this->twoFactorSuccess = __('2FA habilitado correctamente.');
      } catch (ValidationException $exception) {
         throw ValidationException::withMessages([
            'twoFactorForm.code' => __('El código 2FA ingresado no es válido.'),
         ]);
      }
   }

   public function regenerateRecoveryCodes(RegenerateTenantTwoFactorRecoveryCodesAction $action): void {
      $this->twoFactorSuccess = null;
      $this->authorize('update', $this->currentUser);

      $this->currentUser = $action->execute($this->currentUser);
      $this->twoFactorSuccess = __('Códigos de recuperación regenerados.');
   }

   public function disableTwoFactor(DisableTenantTwoFactorAction $action): void {
      $this->twoFactorSuccess = null;
      $this->authorize('update', $this->currentUser);

      $this->currentUser = $action->execute($this->currentUser);
      $this->twoFactorForm->resetCode();
      $this->twoFactorSuccess = __('2FA deshabilitado.');
   }

   public function render(): View {
      return view('workspace::livewire.tenant-profile');
   }
}
