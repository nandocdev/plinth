<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Listeners;

use App\Central\TenantProvisioningModule\Actions\QueueTenantProvisioningHooksAction;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;

final class QueueTenantProvisioningHooksListener {
   public function __construct(
      private readonly QueueTenantProvisioningHooksAction $queueHooks,
   ) {
   }

   public function handle(TenantCreatedFromCentral $event): void {
      $this->queueHooks->execute($event->tenant);
   }
}
