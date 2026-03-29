<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Events;

use App\Central\TenantProvisioningModule\Models\Tenant;

final readonly class TenantCreatedFromCentral {
   public function __construct(
      public Tenant $tenant,
   ) {
   }
}
