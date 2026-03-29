<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire;

use App\Central\TenantProvisioningModule\Actions\CreateTenantAction;
use App\Central\TenantProvisioningModule\Actions\DeleteTenantAction;
use App\Central\TenantProvisioningModule\Actions\ListTenantsAction;
use App\Central\TenantProvisioningModule\Actions\SuspendTenantAction;
use App\Central\TenantProvisioningModule\DTOs\CreateTenantData;
use App\Central\TenantProvisioningModule\DTOs\SuspendTenantData;
use App\Central\TenantProvisioningModule\Livewire\Forms\CreateTenantForm;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

final class TenantCrud extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public CreateTenantForm $form;

   public string $search = '';

   public int $perPage = 15;

   public function mount(): void {
      $this->authorize('viewAny', Tenant::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function createTenant(CreateTenantAction $action): void {
      $this->authorize('create', Tenant::class);

      $payload = $this->form->payload();
      $dto = CreateTenantData::fromValues($payload['name'], $payload['primaryDomain']);

      $action->execute($dto);

      $this->form->clear();
      session()->flash('status', 'Tenant creado correctamente.');
      $this->resetPage();
   }

   public function suspendTenant(string $tenantId, SuspendTenantAction $action): void {
      /** @var Tenant $tenant */
      $tenant = Tenant::query()->findOrFail($tenantId);

      $this->authorize('update', $tenant);

      $shouldSuspend = $tenant->status() !== 'suspended';
      $action->execute(new SuspendTenantData($tenant->id, $shouldSuspend));

      session()->flash('status', $shouldSuspend ? 'Tenant suspendido.' : 'Tenant reactivado.');
   }

   public function deleteTenant(string $tenantId, DeleteTenantAction $action): void {
      /** @var Tenant $tenant */
      $tenant = Tenant::query()->findOrFail($tenantId);

      $this->authorize('delete', $tenant);
      $action->execute($tenant->id);

      session()->flash('status', 'Tenant eliminado correctamente.');
      $this->resetPage();
   }

   public function render(ListTenantsAction $action): View {
      return view('tenant-provisioning::livewire.tenant-crud', [
         'tenants' => $action->execute($this->search, $this->perPage),
      ]);
   }
}
