<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Livewire;

use App\Central\PartnerWebhookModule\Actions\ListPartnerWebhookDeliveriesAction;
use App\Central\PartnerWebhookModule\Actions\RetryPartnerWebhookDeliveryAction;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Historial de Entregas')]
final class WebhookDeliveries extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public string $search = '';

   public function mount(): void {
      $this->authorize('viewAny', PartnerWebhookDelivery::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function retryDelivery(int $deliveryId, RetryPartnerWebhookDeliveryAction $action): void {
      /** @var PartnerWebhookDelivery $delivery */
      $delivery = PartnerWebhookDelivery::query()->findOrFail($deliveryId);
      $this->authorize('retry', $delivery);

      $action->execute($deliveryId);
      session()->flash('status', 'Reintento encolado para la entrega seleccionada.');
   }

   public function render(ListPartnerWebhookDeliveriesAction $listDeliveries): View {
      return view('partner-webhook::livewire.webhook-deliveries', [
         'deliveries' => $listDeliveries->execute($this->search, 15),
      ]);
   }
}
