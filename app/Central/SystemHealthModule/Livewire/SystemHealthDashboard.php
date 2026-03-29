<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\Livewire;

use App\Central\SystemHealthModule\Actions\BuildSystemHealthSnapshotAction;
use App\Central\SystemHealthModule\Actions\FilterSystemHealthSnapshotAction;
use App\Central\SystemHealthModule\DTOs\SystemHealthFilterData;
use App\Central\SystemHealthModule\Livewire\Forms\SystemHealthFilterForm;
use App\Central\SystemHealthModule\Models\SystemHealthSnapshot;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('System Health Dashboard')]
final class SystemHealthDashboard extends Component {
   use AuthorizesRequests;

   public SystemHealthFilterForm $filterForm;

   public function mount(): void {
      $this->authorize('viewAny', SystemHealthSnapshot::class);
   }

   public function clearFilters(): void {
      $this->filterForm->reset();
      $this->filterForm->connectionFilter = 'all';
      $this->filterForm->onlyUnhealthy = false;
   }

   public function render(
      BuildSystemHealthSnapshotAction $build,
      FilterSystemHealthSnapshotAction $filter,
   ): View {
      $filterPayload = $this->filterForm->payload();

      $snapshot = $build->execute();
      $snapshot = $filter->execute($snapshot, new SystemHealthFilterData(
         connectionFilter: $filterPayload['connectionFilter'],
         onlyUnhealthy: $filterPayload['onlyUnhealthy'],
      ));

      return view('system-health::livewire.system-health-dashboard', [
         'snapshot' => $snapshot,
      ]);
   }
}
