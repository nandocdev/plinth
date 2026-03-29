<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Tenant\SelfServiceBillingModule\Livewire\Forms\TenantLoginForm;

#[Layout('layouts.auth')]
#[Title('Acceso — Portal de Facturación')]
final class TenantLogin extends Component {
   public TenantLoginForm $form;

   public function mount(): void {
      if (Auth::guard('tenant')->check()) {
         $this->redirect(route('tenant.billing.portal'), navigate: true);
      }
   }

   public function login(): void {
      $this->validate();

      $throttleKey = Str::lower($this->form->email) . '|' . request()->ip();

      if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
         $seconds = RateLimiter::availableIn($throttleKey);
         $this->addError('form.email', "Demasiados intentos. Inténtalo en {$seconds} segundos.");
         return;
      }

      if (! Auth::guard('tenant')->attempt(
         ['email' => $this->form->email, 'password' => $this->form->password],
         $this->form->remember,
      )) {
         RateLimiter::hit($throttleKey, 60);
         throw ValidationException::withMessages(['form.email' => 'Credenciales incorrectas.']);
      }

      RateLimiter::clear($throttleKey);

      $this->redirect(route('tenant.billing.portal'), navigate: true);
   }

   public function render(): View {
      return view('self-service::livewire.tenant-login');
   }
}
