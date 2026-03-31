<?php

declare(strict_types=1);

namespace App\Tenant\UserManagementModule\Actions;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\UserManagementModule\Events\TenantUserDeleted;
use Illuminate\Support\Facades\DB;

final class DeleteTenantUserAction {
   /**
    * @throws \RuntimeException si el usuario intenta auto-eliminarse
    */
   public function execute(User $target, User $actor): void {
      if ($target->id === $actor->id) {
         throw new \RuntimeException('Un usuario no puede eliminarse a sí mismo.');
      }

      DB::transaction(function () use ($target): void {
         event(new TenantUserDeleted($target));
         $target->delete();
      });
   }
}
