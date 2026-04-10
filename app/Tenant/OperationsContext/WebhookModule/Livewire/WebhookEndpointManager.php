<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Livewire;

use App\Tenant\OperationsContext\WebhookModule\Actions\CreateWebhookEndpointAction;
use App\Tenant\OperationsContext\WebhookModule\Actions\DeleteWebhookEndpointAction;
use App\Tenant\OperationsContext\WebhookModule\Actions\RetryWebhookDeliveryAction;
use App\Tenant\OperationsContext\WebhookModule\Actions\UpdateWebhookEndpointAction;
use App\Tenant\OperationsContext\WebhookModule\DTOs\CreateWebhookEndpointData;
use App\Tenant\OperationsContext\WebhookModule\DTOs\UpdateWebhookEndpointData;
use App\Tenant\OperationsContext\WebhookModule\Enums\TenantWebhookEvent;
use App\Tenant\OperationsContext\WebhookModule\Livewire\Forms\WebhookEndpointForm;
use App\Tenant\OperationsContext\WebhookModule\Models\WebhookDelivery;
use App\Tenant\OperationsContext\WebhookModule\Models\WebhookEndpoint;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Webhooks Salientes')]
final class WebhookEndpointManager extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public WebhookEndpointForm $form;

   public bool $showForm = false;

   public ?string $successMessage = null;

   public ?string $errorMessage = null;

   public function mount(): void {
      $this->authorize('viewAny', WebhookEndpoint::class);
   }

   public function openCreate(): void {
      $this->authorize('create', WebhookEndpoint::class);
      $this->form->clear();
      $this->showForm = true;
      $this->clearMessages();
   }

   public function openEdit(int $endpointId): void {
      /** @var WebhookEndpoint $endpoint */
      $endpoint = WebhookEndpoint::query()->findOrFail($endpointId);
      $this->authorize('update', $endpoint);
      $this->form->fillFromModel($endpoint);
      $this->showForm = true;
      $this->clearMessages();
   }

   public function cancel(): void {
      $this->form->clear();
      $this->showForm = false;
   }

   public function save(
      CreateWebhookEndpointAction $createAction,
      UpdateWebhookEndpointAction $updateAction,
   ): void {
      $this->form->validate();
      $data = $this->form->toArray();
      $resolvedEndpointId = $this->form->resolvedEndpointId();

      try {
         if ($resolvedEndpointId !== null) {
            $this->authorize('update', WebhookEndpoint::query()->findOrFail($resolvedEndpointId));
            $updateAction->execute(UpdateWebhookEndpointData::fromArray($data));
            $this->successMessage = 'Endpoint actualizado correctamente.';
         } else {
            $this->authorize('create', WebhookEndpoint::class);
            $createAction->execute(CreateWebhookEndpointData::fromArray($data));
            $this->successMessage = 'Endpoint creado correctamente.';
         }

         $this->form->clear();
         $this->showForm = false;
         $this->resetPage();
      } catch (\Throwable $exception) {
         report($exception);
         $this->errorMessage = 'No fue posible guardar el endpoint.';
      }
   }

   public function delete(int $endpointId, DeleteWebhookEndpointAction $action): void {
      /** @var WebhookEndpoint $endpoint */
      $endpoint = WebhookEndpoint::query()->findOrFail($endpointId);
      $this->authorize('delete', $endpoint);

      try {
         $action->execute($endpointId);
         $this->successMessage = 'Endpoint eliminado correctamente.';
         $this->resetPage();
      } catch (\Throwable $exception) {
         report($exception);
         $this->errorMessage = 'No fue posible eliminar el endpoint.';
      }
   }

   public function retry(int $deliveryId, RetryWebhookDeliveryAction $action): void {
      $this->authorize('viewAny', WebhookEndpoint::class);

      try {
         $action->execute($deliveryId);
         $this->successMessage = 'Reintento encolado correctamente.';
      } catch (\Throwable $exception) {
         report($exception);
         $this->errorMessage = 'No fue posible encolar el reintento.';
      }
   }

   public function render(): View {
      $endpoints = WebhookEndpoint::query()
         ->withCount('deliveries')
         ->orderByDesc('created_at')
         ->paginate(10, pageName: 'endpointsPage');

      $deliveries = WebhookDelivery::query()
         ->with('endpoint')
         ->orderByDesc('created_at')
         ->paginate(15, pageName: 'deliveriesPage');

      return view('webhook::livewire.webhook-endpoint-manager', [
         'endpoints' => $endpoints,
         'deliveries' => $deliveries,
         'events' => TenantWebhookEvent::cases(),
      ]);
   }

   private function clearMessages(): void {
      $this->successMessage = null;
      $this->errorMessage = null;
   }
}
