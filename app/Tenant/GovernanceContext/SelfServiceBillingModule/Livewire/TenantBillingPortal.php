<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\Livewire;

use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\GetAvailableUpgradePlansAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\GetCheckoutMethodsForTenantContextAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\GetTenantBillingOverviewAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\ListTenantInvoicesAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\RequestPlanUpgradeAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\DTOs\RequestPlanUpgradeData;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Livewire\Forms\PlanUpgradeForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.tenant')]
#[Title('Portal de Facturación')]
final class TenantBillingPortal extends Component {
   use WithPagination;

   public string $activeTab = 'overview';

   public PlanUpgradeForm $upgradeForm;

   public ?string $upgradeSuccess = null;

   public ?string $upgradeError = null;

   public function mount(): void {
      // Redirect en mount() para que Livewire::test() pueda interceptarlo sin route middleware
      if (! Auth::guard('tenant')->check()) {
         $this->redirect('/login', navigate: true);
      }
   }

   public function logout(): void {
      Auth::guard('tenant')->logout();
      session()->invalidate();
      session()->regenerateToken();

      $this->redirect('/login', navigate: true);
   }

   public function updatedUpgradeFormBillingPeriod(): void {
      // Forzar renderizado y limpiar errores al cambiar periodo
      $this->resetErrorBag('upgradeForm.planId');
   }

   public function requestUpgrade(RequestPlanUpgradeAction $action): void {
      $this->upgradeSuccess = null;
      $this->upgradeError = null;

      $this->upgradeForm->validate();

      /** @var string $tenantId */
      $tenantId = (string) tenant('id');

      try {
         $subscription = $action->execute(new RequestPlanUpgradeData(
            tenantId: $tenantId,
            planId: (int) $this->upgradeForm->planId,
            billingPeriod: $this->upgradeForm->billingPeriod,
            methodType: $this->upgradeForm->methodType,
         ));

         $this->upgradeSuccess = "Plan actualizado correctamente a {$subscription->plan?->name}.";

         $checkoutStatusMessage = $subscription->getAttribute('_checkout_status_message');
         if (is_string($checkoutStatusMessage) && $checkoutStatusMessage !== '') {
            $this->upgradeSuccess .= ' ' . $checkoutStatusMessage;
         }

         $this->activeTab = 'overview';
         $this->upgradeForm->reset();
      } catch (RuntimeException $e) {
         $this->upgradeError = $e->getMessage();
      }
   }

   public function render(
      GetTenantBillingOverviewAction $overviewAction,
      GetAvailableUpgradePlansAction $plansAction,
      ListTenantInvoicesAction $invoicesAction,
      GetCheckoutMethodsForTenantContextAction $checkoutMethodsAction,
   ): View {
      $tenantId = (string) tenant('id');

      $checkoutMethods = $checkoutMethodsAction->execute($tenantId);

      if ($this->upgradeForm->methodType === '' && $checkoutMethods !== []) {
         $firstMethod = $checkoutMethods[0]['method_type'] ?? null;
         if (is_string($firstMethod) && $firstMethod !== '') {
            $this->upgradeForm->methodType = $firstMethod;
         }
      }

      return view('self-service::livewire.tenant-billing-portal', [
         'overview' => $overviewAction->execute($tenantId),
         'plans' => $plansAction->execute(),
         'invoices' => $invoicesAction->execute($tenantId),
         'checkoutMethods' => $checkoutMethods,
      ]);
   }
}
