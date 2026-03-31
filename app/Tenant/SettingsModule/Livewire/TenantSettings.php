<?php

declare(strict_types=1);

namespace App\Tenant\SettingsModule\Livewire;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\SettingsModule\Actions\GetTenantSettingsAction;
use App\Tenant\SettingsModule\Actions\UpdateTenantSettingsAction;
use App\Tenant\SettingsModule\Livewire\Forms\TenantSettingsForm;
use App\Tenant\SettingsModule\Models\TenantSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Configuración del tenant')]
final class TenantSettings extends Component {
   public TenantSettingsForm $form;

   public ?string $successMessage = null;

   public function mount(GetTenantSettingsAction $action): void {
      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      if (! $user instanceof User) {
         $this->redirect('/login', navigate: true);

         return;
      }

      $this->authorize('viewAny', TenantSetting::class);

      $this->form->fillFromData($action->execute());
   }

   public function save(UpdateTenantSettingsAction $action): void {
      $this->successMessage = null;

      $this->authorize('update', TenantSetting::class);
      $this->form->validate();

      $updated = $action->execute($this->form->toData());
      $this->form->fillFromData($updated);

      $this->successMessage = 'Configuración actualizada correctamente.';
   }

   public function render(): View {
      return view('settings::livewire.tenant-settings');
   }
}
