<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Livewire;

use App\Central\BillingModule\Actions\ListInvoicesAction;
use App\Central\BillingModule\Models\TenantInvoice;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Historial de Facturas')]
final class InvoiceManagement extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public string $search = '';

   public ?int $selectedInvoiceId = null;

   public function mount(): void {
      $this->authorize('viewAny', TenantInvoice::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function showProforma(int $invoiceId): void {
      $invoice = TenantInvoice::query()->with(['subscription.tenant', 'subscription.plan'])->findOrFail($invoiceId);
      $this->authorize('view', $invoice);
      
      $this->selectedInvoiceId = $invoice->id;
   }

   public function render(ListInvoicesAction $action): View {
      $selectedInvoice = $this->selectedInvoiceId 
         ? TenantInvoice::query()->with(['subscription.tenant', 'subscription.plan'])->find($this->selectedInvoiceId)
         : null;

      return view('billing::livewire.invoice-management', [
         'invoices' => $action->execute($this->search, 15),
         'selectedInvoice' => $selectedInvoice,
      ]);
   }
}
