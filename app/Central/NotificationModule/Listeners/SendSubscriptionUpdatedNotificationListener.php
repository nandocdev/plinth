<?php

declare(strict_types=1);

namespace App\Central\NotificationModule\Listeners;

use App\Central\BillingModule\Events\SubscriptionUpdated;
use App\Central\NotificationModule\Actions\ListCentralNotificationRecipientsAction;
use App\Central\NotificationModule\Notifications\CentralEventMailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final class SendSubscriptionUpdatedNotificationListener implements ShouldQueue {
   public string $queue = 'emails';

   public function handle(SubscriptionUpdated $event): void {
      $recipients = app(ListCentralNotificationRecipientsAction::class)->execute();

      if ($recipients->isEmpty()) {
         return;
      }

      Notification::send($recipients, new CentralEventMailNotification(
         subject: 'Suscripcion actualizada',
         lines: [
            'Se actualizo una suscripcion en central.',
            'Tenant ID: ' . $event->subscription->tenant_id,
            'Estado anterior: ' . $event->previousStatus,
            'Estado actual: ' . $event->subscription->status,
         ],
      ));
   }
}
