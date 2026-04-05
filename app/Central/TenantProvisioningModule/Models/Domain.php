<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Models;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

final class Domain extends BaseDomain {
   protected $guarded = [];

   protected $casts = [
      'verified_at' => 'datetime',
      'ssl_requested_at' => 'datetime',
      'ssl_issued_at' => 'datetime',
      'ssl_expires_at' => 'datetime',
   ];
}
