<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\ActivityLogModule\Livewire;

use App\Tenant\OperationsContext\ActivityLogModule\Actions\ListTenantActivityLogsAction;
use App\Tenant\OperationsContext\ActivityLogModule\DTOs\ListTenantLogsFilterData;
use App\Tenant\OperationsContext\ActivityLogModule\Livewire\Forms\TenantLogsFilterForm;
use App\Tenant\OperationsContext\ActivityLogModule\Models\TenantActivityLogEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Activity log tenant')]
final class TenantLogsViewer extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public TenantLogsFilterForm $filterForm;

   public function mount(): void {
      $this->authorize('viewAny', TenantActivityLogEntry::class);
   }

   public function updated(string $property): void {
      if (str_starts_with($property, 'filterForm.')) {
         $this->resetPage();
      }
   }

   public function clearFilters(): void {
      $this->filterForm->reset();
      $this->filterForm->perPage = 20;
      $this->resetPage();
   }

   public function render(ListTenantActivityLogsAction $listLogs): View {
      $payload = $this->filterForm->payload();

      return view('tenant-activity-log::livewire.tenant-logs-viewer', [
         'logs' => $listLogs->execute(new ListTenantLogsFilterData(
            event: $payload['event'],
            search: $payload['search'],
            perPage: $payload['perPage'],
            page: $this->getPage(),
            tenantId: $this->resolveTenantId(),
         )),
      ]);
   }

   private function resolveTenantId(): string {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para activity log.');
      }

      return $tenantId;
   }
}
