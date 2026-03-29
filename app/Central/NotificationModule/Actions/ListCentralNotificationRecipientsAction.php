<?php

declare(strict_types=1);

namespace App\Central\NotificationModule\Actions;

use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Collection;

final class ListCentralNotificationRecipientsAction {
   /**
    * @return Collection<int, User>
    */
   public function execute(): Collection {
      return User::query()
         ->whereNotNull('email_verified_at')
         ->orderBy('id')
         ->get(['id', 'name', 'email', 'email_verified_at']);
   }
}
