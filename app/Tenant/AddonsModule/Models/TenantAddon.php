<?php

declare(strict_types=1);

namespace App\Tenant\AddonsModule\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantAddon extends Model {
   protected $table = 'tenant_addons';

   protected $fillable = [
      'addon_slug',
      'is_active',
      'config',
      'installed_at',
      'uninstalled_at',
   ];

   protected $casts = [
      'is_active'      => 'boolean',
      'config'         => 'array',
      'installed_at'   => 'datetime',
      'uninstalled_at' => 'datetime',
   ];
}
