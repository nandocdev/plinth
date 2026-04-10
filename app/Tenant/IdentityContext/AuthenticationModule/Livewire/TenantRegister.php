<?php

declare(strict_types=1);

namespace App\Tenant\IdentityContext\AuthenticationModule\Livewire;

use App\Tenant\IdentityContext\AuthenticationModule\Actions\RegisterTenantUserAction;
use App\Tenant\IdentityContext\AuthenticationModule\DTOs\RegisterTenantUserData;
use App\Tenant\IdentityContext\AuthenticationModule\Livewire\Forms\TenantRegisterForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Registro Tenant')]
final class TenantRegister extends Component {
   public TenantRegisterForm $form;

   public function mount(): void {
      if (Auth::guard('tenant')->check()) {
         $this->redirect('/dashboard', navigate: true);
      }
   }

   public function register(RegisterTenantUserAction $action): void {
      $this->validate();

      $user = $action->execute(new RegisterTenantUserData(
         name: trim($this->form->name),
         email: trim(strtolower($this->form->email)),
         password: $this->form->password,
      ));

      Auth::guard('tenant')->login($user);

      if (request()->hasSession()) {
         request()->session()->regenerate();
      }

      $this->redirect('/dashboard', navigate: true);
   }

   public function render(): View {
      return view('tenant-auth::livewire.tenant-register');
   }
}
