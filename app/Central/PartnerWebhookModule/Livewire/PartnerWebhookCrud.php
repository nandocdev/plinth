<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Livewire;

use App\Central\PartnerWebhookModule\Actions\CreatePartnerWebhookEndpointAction;
use App\Central\PartnerWebhookModule\Actions\ListPartnerWebhookDeliveriesAction;
use App\Central\PartnerWebhookModule\Actions\ListPartnerWebhookEndpointsAction;
use App\Central\PartnerWebhookModule\Actions\RetryPartnerWebhookDeliveryAction;
use App\Central\PartnerWebhookModule\Actions\TogglePartnerWebhookEndpointStatusAction;
use App\Central\PartnerWebhookModule\DTOs\CreatePartnerWebhookEndpointData;
use App\Central\PartnerWebhookModule\Enums\PartnerWebhookEvent;
use App\Central\PartnerWebhookModule\Livewire\Forms\PartnerWebhookEndpointForm;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Partner Outbound Webhooks')]
final class PartnerWebhookCrud extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public PartnerWebhookEndpointForm $form;

   public string $endpointsSearch = '';

   public string $deliveriesSearch = '';

   public function mount(): void {
      $this->authorize('viewAny', PartnerWebhookEndpoint::class);
      $this->authorize('viewAny', PartnerWebhookDelivery::class);
   }

   public function updatedEndpointsSearch(): void {
      $this->resetPage('endpointsPage');
   }

   public function updatedDeliveriesSearch(): void {
      $this->resetPage('deliveriesPage');
   }

   public function createEndpoint(CreatePartnerWebhookEndpointAction $action): void {
      $this->authorize('create', PartnerWebhookEndpoint::class);

      $payload = $this->form->payload();

      $action->execute(new CreatePartnerWebhookEndpointData(
         name: $payload['name'],
         targetUrl: $payload['targetUrl'],
         signingSecret: $payload['signingSecret'],
         subscribedEvents: $payload['subscribedEvents'],
         isActive: $payload['isActive'],
      ));

      $this->form->clear();
      $this->resetPage('endpointsPage');
      session()->flash('status', 'Endpoint webhook creado correctamente.');
   }

   public function toggleEndpointStatus(int $endpointId, TogglePartnerWebhookEndpointStatusAction $action): void {
      /** @var PartnerWebhookEndpoint $endpoint */
      $endpoint = PartnerWebhookEndpoint::query()->findOrFail($endpointId);
      $this->authorize('update', $endpoint);

      $action->execute($endpointId, ! $endpoint->is_active);

      session()->flash('status', 'Estado del endpoint actualizado.');
   }

   public function retryDelivery(int $deliveryId, RetryPartnerWebhookDeliveryAction $action): void {
      /** @var PartnerWebhookDelivery $delivery */
      $delivery = PartnerWebhookDelivery::query()->findOrFail($deliveryId);
      $this->authorize('retry', $delivery);

      $action->execute($deliveryId);

      session()->flash('status', 'Reintento encolado para la entrega seleccionada.');
   }

   public function render(
      ListPartnerWebhookEndpointsAction $listEndpoints,
      ListPartnerWebhookDeliveriesAction $listDeliveries,
   ): View {
      return view('partner-webhook::livewire.partner-webhook-crud', [
         'events' => PartnerWebhookEvent::cases(),
         'endpoints' => $listEndpoints->execute($this->endpointsSearch, 10, 'endpointsPage'),
         'deliveries' => $listDeliveries->execute($this->deliveriesSearch, 10, 'deliveriesPage'),
      ]);
   }
}
