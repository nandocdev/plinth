<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\ExportImportModule\Livewire;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\PlatformContext\ExportImportModule\Actions\ListTenantCsvTransferRunsAction;
use App\Tenant\PlatformContext\ExportImportModule\Actions\QueueTenantCsvExportAction;
use App\Tenant\PlatformContext\ExportImportModule\Actions\QueueTenantCsvImportAction;
use App\Tenant\PlatformContext\ExportImportModule\DTOs\QueueTenantCsvExportData;
use App\Tenant\PlatformContext\ExportImportModule\DTOs\QueueTenantCsvImportData;
use App\Tenant\PlatformContext\ExportImportModule\Livewire\Forms\TenantCsvImportForm;
use App\Tenant\PlatformContext\ExportImportModule\Models\TenantCsvTransferRun;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Export/Import CSV')]
final class TenantCsvTransferCenter extends Component {
   use WithPagination;
   use WithFileUploads;

   public TenantCsvImportForm $form;

   public int $perPage = 10;
   public ?string $successMessage = null;

   public function mount(): void {
      $this->authorize('viewAny', TenantCsvTransferRun::class);
   }

   public function queueExport(QueueTenantCsvExportAction $action): void {
      $this->successMessage = null;
      $this->authorize('export', TenantCsvTransferRun::class);

      $tenantId = $this->resolveTenantId();
      $user = $this->resolveTenantUser();

      if (! $user instanceof User) {
         $this->redirect('/login', navigate: true);

         return;
      }

      $action->execute(new QueueTenantCsvExportData(
         tenantId: $tenantId,
         requestedByUserId: $user->id,
      ));

      $this->successMessage = 'Export CSV encolado correctamente.';
      $this->resetPage();
   }

   public function queueImport(QueueTenantCsvImportAction $action): void {
      $this->successMessage = null;
      $this->authorize('import', TenantCsvTransferRun::class);
      $this->form->validate();

      $tenantId = $this->resolveTenantId();
      $user = $this->resolveTenantUser();

      if (! $user instanceof User) {
         $this->redirect('/login', navigate: true);

         return;
      }

      if ($this->form->file === null) {
         throw new \RuntimeException('Archivo CSV no disponible para importacion.');
      }

      $storedPath = $this->form->file->store('csv/imports', 'tenant');

      $action->execute(new QueueTenantCsvImportData(
         tenantId: $tenantId,
         requestedByUserId: $user->id,
         sourceDisk: 'tenant',
         sourcePath: $storedPath,
      ));

      $this->form->resetFile();
      $this->successMessage = 'Import CSV encolado correctamente.';
      $this->resetPage();
   }

   public function render(ListTenantCsvTransferRunsAction $action): View {
      return view('export-import::livewire.tenant-csv-transfer-center', [
         'runs' => $action->execute($this->perPage, $this->getPage()),
      ]);
   }

   private function resolveTenantId(): string {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para CSV transfer.');
      }

      return $tenantId;
   }

   private function resolveTenantUser(): ?User {
      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      return $user;
   }
}
