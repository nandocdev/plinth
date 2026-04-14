<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire;

use App\Central\BillingModule\Actions\ListActivePlansAction;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Actions\CompleteTenantOnboardingAction;
use App\Central\TenantProvisioningModule\Actions\ListTenantProvisioningRegionsAction;
use App\Central\TenantProvisioningModule\DTOs\CompleteTenantOnboardingData;
use App\Central\TenantProvisioningModule\Livewire\Forms\CompleteTenantOnboardingForm;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tenant Onboarding')]
final class TenantOnboardingWizard extends Component {
   use AuthorizesRequests;

   public CompleteTenantOnboardingForm $form;

   public function mount(): void {
      $this->authorize('create', Tenant::class);
      $this->authorize('create', TenantSubscription::class);
      $this->form->region = $this->form->defaultRegion();
   }

   public function onboardTenant(CompleteTenantOnboardingAction $action): void {
      $this->authorize('create', Tenant::class);
      $this->authorize('create', TenantSubscription::class);

      $payload = $this->form->payload();

      $action->execute(new CompleteTenantOnboardingData(
         $payload['name'],
         $payload['primaryDomain'],
         $payload['planId'],
         $payload['billingPeriod'],
         $payload['region'],
         $payload['brandName'],
         $payload['logoUrl'],
         $payload['primaryColor'],
         $payload['secondaryColor'],
         $payload['referralCode'],
      ));

      $this->form->clear();
      session()->flash('status', 'Tenant onboarded correctamente con plan asignado.');

      $this->redirectRoute('central.tenants.index', navigate: true);
   }

   public function render(ListActivePlansAction $listPlans, ListTenantProvisioningRegionsAction $regions): View {
      return view('tenant-provisioning::livewire.tenant-onboarding-wizard', [
         'planOptions' => $listPlans->execute(),
         'regionOptions' => $regions->execute(),
      ]);
   }
}
