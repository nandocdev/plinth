<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire;

use App\Central\TenantProvisioningModule\Actions\CreateTenantAction;
use App\Central\TenantProvisioningModule\Actions\CreateDomainAction;
use App\Central\TenantProvisioningModule\Actions\DeleteTenantAction;
use App\Central\TenantProvisioningModule\Actions\DeleteDomainAction;
use App\Central\TenantProvisioningModule\Actions\ListTenantsAction;
use App\Central\TenantProvisioningModule\Actions\SuspendTenantAction;
use App\Central\TenantProvisioningModule\Actions\VerifyDomainAction;
use App\Central\TenantProvisioningModule\DTOs\CreateDomainData;
use App\Central\TenantProvisioningModule\DTOs\CreateTenantData;
use App\Central\TenantProvisioningModule\DTOs\DeleteDomainData;
use App\Central\TenantProvisioningModule\DTOs\SuspendTenantData;
use App\Central\TenantProvisioningModule\DTOs\VerifyDomainData;
use App\Central\TenantProvisioningModule\Livewire\Forms\CreateDomainForm;
use App\Central\TenantProvisioningModule\Livewire\Forms\CreateTenantForm;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tenant Management')]
final class TenantCrud extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public CreateTenantForm $form;

   public CreateDomainForm $domainForm;

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

   public function createDomain(CreateDomainAction $action): void {
      $this->authorize('create', Domain::class);

      $payload = $this->domainForm->payload();

      $action->execute(CreateDomainData::fromValues($payload['tenantId'], $payload['domain']));

      $this->domainForm->clear();
      session()->flash('status', 'Dominio agregado correctamente.');
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

   public function verifyDomain(string $tenantId, int $domainId, VerifyDomainAction $action): void {
      /** @var Domain $domain */
      $domain = Domain::query()
         ->where('id', $domainId)
         ->where('tenant_id', $tenantId)
         ->firstOrFail();

      $this->authorize('update', $domain);

      $shouldVerify = $domain->verified_at === null;
      $action->execute(new VerifyDomainData($tenantId, $domainId, $shouldVerify));

      session()->flash('status', $shouldVerify ? 'Dominio verificado.' : 'Verificacion removida.');
   }

   public function deleteDomain(string $tenantId, int $domainId, DeleteDomainAction $action): void {
      /** @var Domain $domain */
      $domain = Domain::query()
         ->where('id', $domainId)
         ->where('tenant_id', $tenantId)
         ->firstOrFail();

      $this->authorize('delete', $domain);
      $action->execute(new DeleteDomainData($tenantId, $domainId));

      session()->flash('status', 'Dominio eliminado correctamente.');
   }

   public function render(ListTenantsAction $action): View {
      return view('tenant-provisioning::livewire.tenant-crud', [
         'tenants' => $action->execute($this->search, $this->perPage),
      ]);
   }
}
