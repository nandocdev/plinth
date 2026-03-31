<?php

declare(strict_types=1);

namespace App\Tenant\NotificationModule\Actions;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\NotificationModule\Models\TenantNotification;
use Illuminate\Support\Facades\DB;

final class MarkTenantNotificationAsReadAction {
   public function execute(User $user, string $notificationId): void {
      DB::transaction(function () use ($user, $notificationId): void {
         $notification = TenantNotification::query()
            ->where('id', $notificationId)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->firstOrFail();

         if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
         }
      });
   }
}
