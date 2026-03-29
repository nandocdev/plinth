<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Livewire;

use App\Central\BillingModule\Actions\CreatePlanAction;
use App\Central\BillingModule\Actions\CreateSubscriptionAction;
use App\Central\BillingModule\Actions\DeletePlanAction;
use App\Central\BillingModule\Actions\DeleteSubscriptionAction;
use App\Central\BillingModule\Actions\FindPlanAction;
use App\Central\BillingModule\Actions\FindSubscriptionAction;
use App\Central\BillingModule\Actions\ListActivePlansAction;
use App\Central\BillingModule\Actions\ListPlansAction;
use App\Central\BillingModule\Actions\ListSubscriptionsAction;
use App\Central\BillingModule\Actions\ListTenantOptionsAction;
use App\Central\BillingModule\Actions\UpdatePlanAction;
use App\Central\BillingModule\Actions\UpdateSubscriptionAction;
use App\Central\BillingModule\DTOs\CreatePlanData;
use App\Central\BillingModule\DTOs\CreateSubscriptionData;
use App\Central\BillingModule\DTOs\UpdatePlanData;
use App\Central\BillingModule\DTOs\UpdateSubscriptionData;
use App\Central\BillingModule\Livewire\Forms\PlanForm;
use App\Central\BillingModule\Livewire\Forms\SubscriptionForm;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

final class BillingCrud extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public PlanForm $planForm;

   public SubscriptionForm $subscriptionForm;

   public ?int $editingPlanId = null;

   public ?int $editingSubscriptionId = null;

   public string $plansSearch = '';

   public string $subscriptionsSearch = '';

   public function mount(): void {
      $this->authorize('viewAny', Plan::class);
      $this->authorize('viewAny', TenantSubscription::class);
   }

   public function updatedPlansSearch(): void {
      $this->resetPage('plansPage');
   }

   public function updatedSubscriptionsSearch(): void {
      $this->resetPage('subscriptionsPage');
   }

   public function createPlan(CreatePlanAction $action): void {
      $this->authorize('create', Plan::class);

      $payload = $this->planForm->payload();

      $action->execute(new CreatePlanData(
         $payload['name'],
         $payload['slug'],
         $payload['priceMonthlyCents'],
         $payload['priceYearlyCents'],
         $payload['trialDays'],
         $payload['features'],
         $payload['isActive'],
         $payload['sortOrder'],
      ));

      $this->planForm->clear();
      session()->flash('status', 'Plan creado correctamente.');
      $this->resetPage('plansPage');
   }

   public function startPlanEditing(int $planId, FindPlanAction $action): void {
      $plan = $action->execute($planId);
      $this->authorize('update', $plan);

      $this->editingPlanId = $plan->id;
      $this->planForm->fillFromPlan($plan->toArray());
   }

   public function updatePlan(UpdatePlanAction $action): void {
      if ($this->editingPlanId === null) {
         return;
      }

      $plan = app(FindPlanAction::class)->execute($this->editingPlanId);
      $this->authorize('update', $plan);

      $payload = $this->planForm->payload($this->editingPlanId);

      $action->execute(new UpdatePlanData(
         $this->editingPlanId,
         $payload['name'],
         $payload['slug'],
         $payload['priceMonthlyCents'],
         $payload['priceYearlyCents'],
         $payload['trialDays'],
         $payload['features'],
         $payload['isActive'],
         $payload['sortOrder'],
      ));

      $this->cancelPlanEditing();
      session()->flash('status', 'Plan actualizado correctamente.');
   }

   public function cancelPlanEditing(): void {
      $this->editingPlanId = null;
      $this->planForm->clear();
   }

   public function deletePlan(int $planId, DeletePlanAction $action): void {
      $plan = app(FindPlanAction::class)->execute($planId);
      $this->authorize('delete', $plan);

      $action->execute($planId);

      if ($this->editingPlanId === $planId) {
         $this->cancelPlanEditing();
      }

      session()->flash('status', 'Plan eliminado correctamente.');
      $this->resetPage('plansPage');
   }

   public function createSubscription(CreateSubscriptionAction $action): void {
      $this->authorize('create', TenantSubscription::class);

      $payload = $this->subscriptionForm->payload();

      $action->execute(new CreateSubscriptionData(
         $payload['tenantId'],
         $payload['planId'],
         $payload['billingPeriod'],
         $payload['status'],
         $payload['trialEndsAt'],
      ));

      $this->subscriptionForm->clear();
      session()->flash('status', 'Suscripcion creada correctamente.');
      $this->resetPage('subscriptionsPage');
   }

   public function startSubscriptionEditing(int $subscriptionId, FindSubscriptionAction $action): void {
      $subscription = $action->execute($subscriptionId);
      $this->authorize('update', $subscription);

      $this->editingSubscriptionId = $subscription->id;
      $this->subscriptionForm->fillFromSubscription($subscription->toArray());
   }

   public function updateSubscription(UpdateSubscriptionAction $action): void {
      if ($this->editingSubscriptionId === null) {
         return;
      }

      $subscription = app(FindSubscriptionAction::class)->execute($this->editingSubscriptionId);
      $this->authorize('update', $subscription);

      $payload = $this->subscriptionForm->payload($this->editingSubscriptionId);

      $action->execute(new UpdateSubscriptionData(
         $this->editingSubscriptionId,
         $payload['tenantId'],
         $payload['planId'],
         $payload['billingPeriod'],
         $payload['status'],
         $payload['trialEndsAt'],
         $payload['endsAt'],
      ));

      $this->cancelSubscriptionEditing();
      session()->flash('status', 'Suscripcion actualizada correctamente.');
   }

   public function cancelSubscriptionEditing(): void {
      $this->editingSubscriptionId = null;
      $this->subscriptionForm->clear();
   }

   public function deleteSubscription(int $subscriptionId, DeleteSubscriptionAction $action): void {
      $subscription = app(FindSubscriptionAction::class)->execute($subscriptionId);
      $this->authorize('delete', $subscription);

      $action->execute($subscriptionId);

      if ($this->editingSubscriptionId === $subscriptionId) {
         $this->cancelSubscriptionEditing();
      }

      session()->flash('status', 'Suscripcion eliminada correctamente.');
      $this->resetPage('subscriptionsPage');
   }

   public function render(
      ListPlansAction $listPlans,
      ListSubscriptionsAction $listSubscriptions,
      ListTenantOptionsAction $listTenants,
      ListActivePlansAction $listActivePlans,
   ): View {
      return view('billing::livewire.billing-crud', [
         'plans' => $listPlans->execute($this->plansSearch, 10, 'plansPage'),
         'subscriptions' => $listSubscriptions->execute($this->subscriptionsSearch, 10, 'subscriptionsPage'),
         'tenantOptions' => $listTenants->execute(),
         'planOptions' => $listActivePlans->execute(),
      ]);
   }
}
