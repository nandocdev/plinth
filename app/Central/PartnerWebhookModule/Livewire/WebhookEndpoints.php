<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Livewire;

use App\Central\PartnerWebhookModule\Actions\CreatePartnerWebhookEndpointAction;
use App\Central\PartnerWebhookModule\Actions\ListPartnerWebhookEndpointsAction;
use App\Central\PartnerWebhookModule\Actions\TogglePartnerWebhookEndpointStatusAction;
use App\Central\PartnerWebhookModule\DTOs\CreatePartnerWebhookEndpointData;
use App\Central\PartnerWebhookModule\Enums\PartnerWebhookEvent;
use App\Central\PartnerWebhookModule\Livewire\Forms\PartnerWebhookEndpointForm;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Configuración de Webhooks')]
final class WebhookEndpoints extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public PartnerWebhookEndpointForm $form;
   public string $search = '';

   public function mount(): void {
      $this->authorize('viewAny', PartnerWebhookEndpoint::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
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
      session()->flash('status', 'Endpoint webhook creado correctamente.');
      $this->resetPage();
   }

   public function toggleEndpointStatus(int $endpointId, TogglePartnerWebhookEndpointStatusAction $action): void {
      /** @var PartnerWebhookEndpoint $endpoint */
      $endpoint = PartnerWebhookEndpoint::query()->findOrFail($endpointId);
      $this->authorize('update', $endpoint);

      $action->execute($endpointId, ! $endpoint->is_active);
      session()->flash('status', 'Estado del endpoint actualizado.');
   }

   public function render(ListPartnerWebhookEndpointsAction $listEndpoints): View {
      return view('partner-webhook::livewire.webhook-endpoints', [
         'events' => PartnerWebhookEvent::cases(),
         'endpoints' => $listEndpoints->execute($this->search, 10),
      ]);
   }
}
