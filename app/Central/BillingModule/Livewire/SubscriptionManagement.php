<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Livewire;

use App\Central\BillingModule\Actions\CreateSubscriptionAction;
use App\Central\BillingModule\Actions\DeleteSubscriptionAction;
use App\Central\BillingModule\Actions\FindSubscriptionAction;
use App\Central\BillingModule\Actions\ListActivePlansAction;
use App\Central\BillingModule\Actions\ListSubscriptionsAction;
use App\Central\BillingModule\Actions\ListTenantOptionsAction;
use App\Central\BillingModule\Actions\SyncSubscriptionLifecycleAction;
use App\Central\BillingModule\Actions\UpdateSubscriptionAction;
use App\Central\BillingModule\DTOs\CreateSubscriptionData;
use App\Central\BillingModule\DTOs\UpdateSubscriptionData;
use App\Central\BillingModule\Livewire\Forms\SubscriptionForm;
use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Gestión de Suscripciones')]
final class SubscriptionManagement extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public SubscriptionForm $subscriptionForm;

   public ?int $editingSubscriptionId = null;

   public string $search = '';

   public function mount(): void {
      $this->authorize('viewAny', TenantSubscription::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
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
      session()->flash('status', 'Suscripción creada correctamente.');
      $this->resetPage();
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
      session()->flash('status', 'Suscripción actualizada correctamente.');
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

      session()->flash('status', 'Suscripción eliminada correctamente.');
      $this->resetPage();
   }

   public function syncSubscriptionLifecycle(SyncSubscriptionLifecycleAction $action): void {
      $this->authorize('viewAny', TenantSubscription::class);

      $migrated = $action->execute();
      session()->flash('status', sprintf('Lifecycle sincronizado. Suscripciones trial->active: %d', $migrated));
      $this->resetPage();
   }

   public function render(
      ListSubscriptionsAction $listSubscriptions,
      ListTenantOptionsAction $listTenants,
      ListActivePlansAction $listActivePlans,
   ): View {
      return view('billing::livewire.subscription-management', [
         'subscriptions' => $listSubscriptions->execute($this->search, 10),
         'tenantOptions' => $listTenants->execute(),
         'planOptions' => $listActivePlans->execute(),
      ]);
   }
}
