<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Livewire;

use App\Tenant\SelfServiceBillingModule\Actions\GetAvailableUpgradePlansAction;
use App\Tenant\SelfServiceBillingModule\Actions\GetTenantBillingOverviewAction;
use App\Tenant\SelfServiceBillingModule\Actions\ListTenantInvoicesAction;
use App\Tenant\SelfServiceBillingModule\Actions\RequestPlanUpgradeAction;
use App\Tenant\SelfServiceBillingModule\DTOs\RequestPlanUpgradeData;
use App\Tenant\SelfServiceBillingModule\Livewire\Forms\PlanUpgradeForm;
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
         $this->redirect('/billing/login', navigate: true);
      }
   }

   public function logout(): void {
      Auth::guard('tenant')->logout();
      session()->invalidate();
      session()->regenerateToken();

      $this->redirect('/', navigate: true);
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
         ));

         $this->upgradeSuccess = "Plan actualizado correctamente a {$subscription->plan?->name}.";
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
   ): View {
      $tenantId = (string) tenant('id');

      return view('self-service::livewire.tenant-billing-portal', [
         'overview' => $overviewAction->execute($tenantId),
         'plans' => $plansAction->execute(),
         'invoices' => $invoicesAction->execute($tenantId),
      ]);
   }
}
