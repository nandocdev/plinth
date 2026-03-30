<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire;

use App\Central\TenantProvisioningModule\Actions\RegisterPublicTenantAction;
use App\Central\TenantProvisioningModule\DTOs\PublicTenantRegistrationData;
use App\Central\TenantProvisioningModule\Livewire\Forms\PublicTenantSignupForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Crear workspace — Plinth')]
final class PublicTenantSignup extends Component {
   public PublicTenantSignupForm $form;

   public ?string $successRedirectUrl = null;

   public function mount(): void {
      $planSlug = request()->query('plan', '');

      if ($planSlug !== '' && $planSlug !== null) {
         $plan = \App\Central\BillingModule\Models\Plan::query()
            ->where('slug', $planSlug)
            ->where('is_active', true)
            ->first();

         if ($plan !== null) {
            $this->form->planId = $plan->id;
         }
      }
   }

   public function register(RegisterPublicTenantAction $action): void {
      $key = 'public-signup:' . request()->ip();

      if (RateLimiter::tooManyAttempts($key, 5)) {
         $this->addError('form.companyName', 'Demasiados intentos de registro. Intenta de nuevo en unos minutos.');

         return;
      }

      $this->form->validate();

      if (! $this->form->validateSubdomainAvailable()) {
         $this->addError('form.subdomain', 'Este subdominio ya está en uso. Elige otro.');

         return;
      }

      RateLimiter::hit($key, 120);

      $tenant = $action->execute(new PublicTenantRegistrationData(
         companyName: $this->form->companyName,
         subdomain: $this->form->subdomain,
         adminName: $this->form->adminName,
         adminEmail: $this->form->adminEmail,
         adminPassword: $this->form->adminPassword,
         planId: $this->form->planId,
      ));

      $baseHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
      $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'http';
      $this->successRedirectUrl = "{$scheme}://{$this->form->subdomain}.{$baseHost}/login";

      RateLimiter::clear($key);
   }

   public function render(): View {
      return view('tenant-provisioning::livewire.public-tenant-signup', [
         'plans' => $this->form->availablePlans(),
         'baseHost' => parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost',
      ]);
   }
}
