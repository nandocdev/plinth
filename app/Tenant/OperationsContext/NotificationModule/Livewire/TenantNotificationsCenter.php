<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\NotificationModule\Livewire;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\OperationsContext\NotificationModule\Actions\ListTenantNotificationsAction;
use App\Tenant\OperationsContext\NotificationModule\Actions\MarkTenantNotificationAsReadAction;
use App\Tenant\OperationsContext\NotificationModule\Actions\SendTenantNotificationAction;
use App\Tenant\OperationsContext\NotificationModule\Livewire\Forms\TenantNotificationComposerForm;
use App\Tenant\OperationsContext\NotificationModule\Models\TenantNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Notificaciones')]
final class TenantNotificationsCenter extends Component {
   use WithPagination;

   public TenantNotificationComposerForm $form;

   public int $perPage = 10;
   public ?string $successMessage = null;

   public function mount(): void {
      $this->authorize('viewAny', TenantNotification::class);
   }

   public function send(SendTenantNotificationAction $action): void {
      $this->successMessage = null;
      $this->authorize('send', TenantNotification::class);
      $this->form->validate();

      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      if (! $user instanceof User) {
         $this->redirect('/login', navigate: true);

         return;
      }

      $count = $action->execute($this->form->toData(), $user);
      $this->form->resetCompose();
      $this->successMessage = $count > 0
         ? 'Notificación enviada a ' . $count . ' usuario(s).'
         : 'No hay destinatarios elegibles para este envío.';
   }

   public function markAsRead(string $notificationId, MarkTenantNotificationAsReadAction $action): void {
      $this->authorize('markRead', TenantNotification::class);

      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      if (! $user instanceof User) {
         $this->redirect('/login', navigate: true);

         return;
      }

      $action->execute($user, $notificationId);
   }

   public function render(ListTenantNotificationsAction $action): View {
      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      if (! $user instanceof User) {
         abort(403);
      }

      return view('notification::livewire.tenant-notifications-center', [
         'notifications' => $action->execute($user, $this->perPage, $this->getPage()),
      ]);
   }
}
