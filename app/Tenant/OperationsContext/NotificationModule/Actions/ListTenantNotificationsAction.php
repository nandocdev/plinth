<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\NotificationModule\Actions;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\OperationsContext\NotificationModule\Models\TenantNotification;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListTenantNotificationsAction {
   public function execute(User $user, int $perPage, int $page): LengthAwarePaginator {
      return TenantNotification::query()
         ->where('notifiable_type', User::class)
         ->where('notifiable_id', $user->id)
         ->orderByDesc('created_at')
         ->paginate(
            perPage: in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
            page: $page,
            pageName: 'page',
         );
   }
}
