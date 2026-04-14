<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Livewire;

use App\Central\BillingModule\Actions\CreatePlanAction;
use App\Central\BillingModule\Actions\DeletePlanAction;
use App\Central\BillingModule\Actions\FindPlanAction;
use App\Central\BillingModule\Actions\ListPlansAction;
use App\Central\BillingModule\Actions\UpdatePlanAction;
use App\Central\BillingModule\DTOs\CreatePlanData;
use App\Central\BillingModule\DTOs\UpdatePlanData;
use App\Central\BillingModule\Livewire\Forms\PlanForm;
use App\Central\BillingModule\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Gestión de Planes')]
final class PlanManagement extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public PlanForm $planForm;

   public ?int $editingPlanId = null;

   public string $search = '';

   public function mount(): void {
      $this->authorize('viewAny', Plan::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
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
         $payload['maxUsersSoft'],
         $payload['maxUsersHard'],
         $payload['maxStorageMbSoft'],
         $payload['maxStorageMbHard'],
         $payload['isActive'],
         $payload['sortOrder'],
      ));

      $this->planForm->clear();
      session()->flash('status', 'Plan creado correctamente.');
      $this->resetPage();
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
         $payload['maxUsersSoft'],
         $payload['maxUsersHard'],
         $payload['maxStorageMbSoft'],
         $payload['maxStorageMbHard'],
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
      $this->resetPage();
   }

   public function render(ListPlansAction $listPlans): View {
      return view('billing::livewire.plan-management', [
         'plans' => $listPlans->execute($this->search, 10),
      ]);
   }
}
