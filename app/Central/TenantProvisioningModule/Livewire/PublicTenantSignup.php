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

   public int $currentStep = 1;

   public int $totalSteps = 4;

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

   public function nextStep(): void {
      $this->validate($this->form->rulesForStep($this->currentStep));

      $this->currentStep = min($this->totalSteps, $this->currentStep + 1);
   }

   public function previousStep(): void {
      $this->currentStep = max(1, $this->currentStep - 1);
   }

   public function goToStep(int $step): void {
      $targetStep = max(1, min($this->totalSteps, $step));

      if ($targetStep <= $this->currentStep) {
         $this->currentStep = $targetStep;

         return;
      }

      // Evita saltos hacia adelante sin validar los pasos intermedios.
      for ($index = $this->currentStep; $index < $targetStep; $index++) {
         $this->validate($this->form->rulesForStep($index));
      }

      $this->currentStep = $targetStep;
   }

   public function register(RegisterPublicTenantAction $action): void {
      $key = 'public-signup:' . request()->ip();

      if (RateLimiter::tooManyAttempts($key, 5)) {
         $this->addError('form.companyName', 'Demasiados intentos de registro. Intenta de nuevo en unos minutos.');

         return;
      }

      $this->validate($this->form->rulesForAllSteps());

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
      $this->currentStep = $this->totalSteps;

      RateLimiter::clear($key);
   }

   public function render(): View {
      return view('tenant-provisioning::livewire.public-tenant-signup', [
         'plans' => $this->form->availablePlans(),
         'baseHost' => parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost',
         'steps' => [
            ['number' => 1, 'label' => 'Empresa', 'description' => 'Nombre y subdominio', 'icon' => 'building-office-2'],
            ['number' => 2, 'label' => 'Plan', 'description' => 'Elige tu suscripción', 'icon' => 'credit-card'],
            ['number' => 3, 'label' => 'Admin', 'description' => 'Cuenta principal', 'icon' => 'user-circle'],
            ['number' => 4, 'label' => 'Confirmar', 'description' => 'Términos y creación', 'icon' => 'check-badge'],
         ],
         'progressPercent' => (int) round(($this->currentStep / $this->totalSteps) * 100),
      ]);
   }
}
