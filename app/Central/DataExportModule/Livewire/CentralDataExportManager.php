<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Livewire;

use App\Central\AuthenticationModule\Models\User;
use App\Central\DataExportModule\Actions\ListCentralDataExportsAction;
use App\Central\DataExportModule\Actions\ListCentralExportTenantOptionsAction;
use App\Central\DataExportModule\Actions\QueueCentralDataExportAction;
use App\Central\DataExportModule\DTOs\RequestCentralDataExportData;
use App\Central\DataExportModule\Livewire\Forms\CentralDataExportRequestForm;
use App\Central\DataExportModule\Models\CentralDataExport;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
#[Title('GDPR Data Exports')]
final class CentralDataExportManager extends Component {
   use AuthorizesRequests;

   public CentralDataExportRequestForm $form;

   public function mount(): void {
      $this->authorize('viewAny', CentralDataExport::class);
   }

   public function requestExport(QueueCentralDataExportAction $action): void {
      $this->authorize('create', CentralDataExport::class);

      $user = auth('central')->user();

      if (! $user instanceof User) {
         abort(403);
      }

      $payload = $this->form->payload();

      try {
         $action->execute(new RequestCentralDataExportData(
            tenantId: $payload['tenantId'],
            requestedByUserId: $user->id,
            includeActivityLog: $payload['includeActivityLog'],
         ));
      } catch (RuntimeException $exception) {
         session()->flash('error', $exception->getMessage());

         return;
      }

      $this->form->clear();
      session()->flash('status', 'Exportacion GDPR encolada correctamente.');
   }

   public function render(
      ListCentralDataExportsAction $exports,
      ListCentralExportTenantOptionsAction $tenants,
   ): View {
      return view('data-export::livewire.central-data-export-manager', [
         'exports' => $exports->execute(),
         'tenantOptions' => $tenants->execute(),
      ]);
   }
}