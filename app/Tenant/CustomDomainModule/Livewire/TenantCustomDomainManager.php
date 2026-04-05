<?php

declare(strict_types=1);

namespace App\Tenant\CustomDomainModule\Livewire;

use App\Tenant\CustomDomainModule\Actions\CreateTenantCustomDomainAction;
use App\Tenant\CustomDomainModule\Actions\ListTenantCustomDomainsAction;
use App\Tenant\CustomDomainModule\Actions\RemoveTenantCustomDomainAction;
use App\Tenant\CustomDomainModule\Actions\RequestTenantDomainSslCertificateAction;
use App\Tenant\CustomDomainModule\Actions\ToggleTenantCustomDomainVerificationAction;
use App\Tenant\CustomDomainModule\DTOs\CreateCustomDomainData;
use App\Tenant\CustomDomainModule\Livewire\Forms\CustomDomainForm;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Dominios personalizados')]
final class TenantCustomDomainManager extends Component {
   use AuthorizesRequests;

   public CustomDomainForm $domainForm;

   public ?string $successMessage = null;
   public ?string $errorMessage = null;

   public function mount(): void {
      $this->authorize('tenant.custom-domains.view');
   }

   public function create(CreateTenantCustomDomainAction $action): void {
      $this->authorize('tenant.custom-domains.manage');
      $this->domainForm->validate();

      $tenantId = $this->resolveTenantId();

      try {
         $action->execute(new CreateCustomDomainData(
            tenantId: $tenantId,
            domain: $this->domainForm->domain,
         ));

         $this->successMessage = 'Dominio agregado correctamente.';
         $this->errorMessage = null;
         $this->domainForm->clear();
      } catch (\Throwable $e) {
         $this->errorMessage = $e->getMessage();
         $this->successMessage = null;
      }
   }

   public function toggleVerification(int $domainId, ToggleTenantCustomDomainVerificationAction $action): void {
      $this->authorize('tenant.custom-domains.manage');

      $action->execute($this->resolveTenantId(), $domainId);

      $this->successMessage = 'Estado de verificación actualizado.';
      $this->errorMessage = null;
   }

   public function requestSsl(int $domainId, RequestTenantDomainSslCertificateAction $action): void {
      $this->authorize('tenant.custom-domains.manage');

      $action->execute($this->resolveTenantId(), $domainId);

      $this->successMessage = 'Emisión SSL solicitada. El certificado se procesará en segundo plano.';
      $this->errorMessage = null;
   }

   public function remove(int $domainId, RemoveTenantCustomDomainAction $action): void {
      $this->authorize('tenant.custom-domains.manage');

      $action->execute($this->resolveTenantId(), $domainId);

      $this->successMessage = 'Dominio eliminado correctamente.';
      $this->errorMessage = null;
   }

   public function render(ListTenantCustomDomainsAction $listDomains): View {
      return view('tenant-custom-domain::livewire.tenant-custom-domain-manager', [
         'domains' => $listDomains->execute($this->resolveTenantId()),
      ]);
   }

   private function resolveTenantId(): string {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para dominios personalizados.');
      }

      return $tenantId;
   }
}
