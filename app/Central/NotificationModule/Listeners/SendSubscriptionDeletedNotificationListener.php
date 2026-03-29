<?php

declare(strict_types=1);

namespace App\Central\NotificationModule\Listeners;

use App\Central\BillingModule\Events\SubscriptionDeleted;
use App\Central\NotificationModule\Actions\ListCentralNotificationRecipientsAction;
use App\Central\NotificationModule\Notifications\CentralEventMailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final class SendSubscriptionDeletedNotificationListener implements ShouldQueue {
   public string $queue = 'emails';

   public function handle(SubscriptionDeleted $event): void {
      $recipients = app(ListCentralNotificationRecipientsAction::class)->execute();

      if ($recipients->isEmpty()) {
         return;
      }

      Notification::send($recipients, new CentralEventMailNotification(
         subject: 'Suscripcion eliminada',
         lines: [
            'Una suscripcion fue marcada como deleted en central.',
            'Tenant ID: ' . $event->subscription->tenant_id,
            'Estado final: ' . $event->subscription->status,
         ],
      ));
   }
}
