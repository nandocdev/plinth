<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Livewire;

use App\Central\ActivityLogModule\Actions\ListAdminLogFilterOptionsAction;
use App\Central\ActivityLogModule\Actions\ListGlobalLogsAction;
use App\Central\ActivityLogModule\Actions\ListTenantLogFilterOptionsAction;
use App\Central\ActivityLogModule\DTOs\ListGlobalLogsFilterData;
use App\Central\ActivityLogModule\Livewire\Forms\GlobalLogsFilterForm;
use App\Central\ActivityLogModule\Models\ActivityLogEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Central Audit Log')]
final class GlobalLogsViewer extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public GlobalLogsFilterForm $filterForm;

   public function mount(): void {
      $this->authorize('viewAny', ActivityLogEntry::class);
   }

   public function updated(string $property): void {
      if (str_starts_with($property, 'filterForm.')) {
         $this->resetPage();
      }
   }

   public function clearFilters(): void {
      $this->filterForm->reset();
      $this->filterForm->perPage = 25;
      $this->resetPage();
   }

   public function render(
      ListGlobalLogsAction $listLogs,
      ListTenantLogFilterOptionsAction $listTenantOptions,
      ListAdminLogFilterOptionsAction $listAdminOptions,
   ): View {
      $payload = $this->filterForm->payload();

      return view('activity-log::livewire.global-logs-viewer', [
         'logs' => $listLogs->execute(new ListGlobalLogsFilterData(
            tenantId: $payload['tenantId'],
            event: $payload['event'],
            causerId: $payload['causerId'],
            search: $payload['search'],
            perPage: $payload['perPage'],
            page: $this->getPage(),
         )),
         'tenantOptions' => $listTenantOptions->execute(),
         'adminOptions' => $listAdminOptions->execute(),
      ]);
   }
}
