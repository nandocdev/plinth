<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Livewire;

use App\Tenant\OperationsContext\WebhookModule\Actions\CreateIncomingTokenAction;
use App\Tenant\OperationsContext\WebhookModule\Actions\RevokeIncomingTokenAction;
use App\Tenant\OperationsContext\WebhookModule\DTOs\CreateIncomingTokenData;
use App\Tenant\OperationsContext\WebhookModule\Livewire\Forms\IncomingTokenForm;
use App\Tenant\OperationsContext\WebhookModule\Models\IncomingWebhookLog;
use App\Tenant\OperationsContext\WebhookModule\Models\IncomingWebhookToken;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Webhooks Entrantes')]
final class IncomingWebhookManager extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public IncomingTokenForm $form;

   public bool $showForm = false;

   public ?string $successMessage = null;

   public ?string $errorMessage = null;

   public function mount(): void {
      $this->authorize('viewAny', IncomingWebhookToken::class);
   }

   public function openCreate(): void {
      $this->authorize('create', IncomingWebhookToken::class);
      $this->form->clear();
      $this->showForm = true;
      $this->clearMessages();
   }

   public function cancel(): void {
      $this->form->clear();
      $this->showForm = false;
   }

   public function create(CreateIncomingTokenAction $action): void {
      $this->authorize('create', IncomingWebhookToken::class);
      $this->form->validate();

      try {
         $action->execute(CreateIncomingTokenData::fromArray($this->form->toArray()));
         $this->successMessage = 'Token creado. Copia la URL antes de navegar.';
         $this->form->clear();
         $this->showForm = false;
         $this->resetPage();
      } catch (\Throwable $exception) {
         report($exception);
         $this->errorMessage = 'No fue posible crear el token.';
      }
   }

   public function revoke(int $tokenId, RevokeIncomingTokenAction $action): void {
      /** @var IncomingWebhookToken $token */
      $token = IncomingWebhookToken::query()->findOrFail($tokenId);
      $this->authorize('delete', $token);

      try {
         $action->execute($tokenId);
         $this->successMessage = 'Token revocado correctamente.';
      } catch (\Throwable $exception) {
         report($exception);
         $this->errorMessage = 'No fue posible revocar el token.';
      }
   }

   public function render(): View {
      $tokens = IncomingWebhookToken::query()
         ->withCount('logs')
         ->orderByDesc('created_at')
         ->paginate(10, pageName: 'tokensPage');

      $logs = IncomingWebhookLog::query()
         ->with('token')
         ->orderByDesc('created_at')
         ->paginate(15, pageName: 'logsPage');

      return view('webhook::livewire.incoming-webhook-manager', [
         'tokens' => $tokens,
         'logs' => $logs,
      ]);
   }

   private function clearMessages(): void {
      $this->successMessage = null;
      $this->errorMessage = null;
   }
}
