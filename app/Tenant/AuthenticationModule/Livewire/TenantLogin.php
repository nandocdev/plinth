<?php

declare(strict_types=1);

namespace App\Tenant\AuthenticationModule\Livewire;

use App\Tenant\AuthenticationModule\Actions\AuthenticateTenantUserAction;
use App\Tenant\AuthenticationModule\DTOs\AuthenticateTenantUserData;
use App\Tenant\AuthenticationModule\Livewire\Forms\TenantLoginForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Acceso Tenant')]
final class TenantLogin extends Component {
   public TenantLoginForm $form;

   public function mount(): void {
      if (Auth::guard('tenant')->check()) {
         $this->redirect('/dashboard', navigate: true);
      }
   }

   public function login(AuthenticateTenantUserAction $action): void {
      $this->validate();

      $action->execute(new AuthenticateTenantUserData(
         email: $this->form->email,
         password: $this->form->password,
         remember: $this->form->remember,
         ipAddress: (string) (request()->ip() ?? 'unknown-ip'),
      ));

      if (request()->hasSession()) {
         request()->session()->regenerate();
      }

      $this->redirect('/dashboard', navigate: true);
   }

   public function render(): View {
      return view('tenant-auth::livewire.tenant-login');
   }
}
