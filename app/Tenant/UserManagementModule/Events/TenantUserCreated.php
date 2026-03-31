<?php

declare(strict_types=1);

namespace App\Tenant\UserManagementModule\Events;

use App\Tenant\AuthenticationModule\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TenantUserCreated {
   use Dispatchable, SerializesModels;

   public function __construct(
      public readonly User $user,
   ) {
   }
}
