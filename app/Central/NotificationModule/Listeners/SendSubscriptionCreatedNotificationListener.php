<?php

declare(strict_types=1);

namespace App\Central\NotificationModule\Listeners;

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Central\NotificationModule\Actions\ListCentralNotificationRecipientsAction;
use App\Central\NotificationModule\Notifications\CentralEventMailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final class SendSubscriptionCreatedNotificationListener implements ShouldQueue {
   public string $queue = 'emails';

   public function handle(SubscriptionCreated $event): void {
      $recipients = app(ListCentralNotificationRecipientsAction::class)->execute();

      if ($recipients->isEmpty()) {
         return;
      }

      Notification::send($recipients, new CentralEventMailNotification(
         subject: 'Nueva suscripcion registrada',
         lines: [
            'Se registro una nueva suscripcion en central.',
            'Tenant ID: ' . $event->subscription->tenant_id,
            'Estado: ' . $event->subscription->status,
            'Periodo: ' . $event->subscription->billing_period,
         ],
      ));
   }
}
