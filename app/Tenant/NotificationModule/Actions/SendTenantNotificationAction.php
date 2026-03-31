<?php

declare(strict_types=1);

namespace App\Tenant\NotificationModule\Actions;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\NotificationModule\DTOs\SendTenantNotificationData;
use App\Tenant\NotificationModule\Notifications\TenantMailDatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

final class SendTenantNotificationAction {
   public function execute(SendTenantNotificationData $data, User $actor): int {
      $query = User::query()->whereNotNull('email_verified_at');

      if ($data->targetRole !== 'all') {
         $query->role($data->targetRole, 'tenant');
      }

      $recipients = $query->get(['id', 'name', 'email']);

      if ($recipients->isEmpty()) {
         return 0;
      }

      return DB::transaction(function () use ($data, $actor, $recipients): int {
         Notification::send($recipients, new TenantMailDatabaseNotification(
            subject: $data->subject,
            message: $data->message,
            sentBy: $actor->name,
         ));

         Log::info('tenant.notifications.sent', [
            'tenant_id' => tenant()?->id,
            'sent_by' => $actor->id,
            'target_role' => $data->targetRole,
            'recipient_count' => $recipients->count(),
         ]);

         return $recipients->count();
      });
   }
}
