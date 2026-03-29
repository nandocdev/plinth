<?php

declare(strict_types=1);

namespace App\Central\NotificationModule\Listeners;

use App\Central\NotificationModule\Actions\ListCentralNotificationRecipientsAction;
use App\Central\NotificationModule\Notifications\CentralEventMailNotification;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final class SendNewTenantNotificationListener implements ShouldQueue {
   public string $queue = 'emails';

   public function handle(TenantCreatedFromCentral $event): void {
      $recipients = app(ListCentralNotificationRecipientsAction::class)->execute();

      if ($recipients->isEmpty()) {
         return;
      }

      Notification::send($recipients, new CentralEventMailNotification(
         subject: 'Nuevo tenant creado en central',
         lines: [
            'Se ha creado un nuevo tenant desde el panel central.',
            'Tenant ID: ' . $event->tenant->id,
            'Nombre: ' . $event->tenant->displayName(),
         ],
      ));
   }
}
