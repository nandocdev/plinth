<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\AddonsModule\Actions;

use App\Tenant\GovernanceContext\AddonsModule\Enums\AvailableAddon;
use App\Tenant\GovernanceContext\AddonsModule\Models\TenantAddon;
use Illuminate\Support\Facades\DB;

final class InstallAddonAction {
   public function execute(AvailableAddon $addon): TenantAddon {
      return DB::transaction(function () use ($addon): TenantAddon {
         /** @var TenantAddon $record */
         $record = TenantAddon::query()->updateOrCreate(
            ['addon_slug' => $addon->value],
            [
               'is_active'      => true,
               'installed_at'   => now(),
               'uninstalled_at' => null,
            ],
         );

         return $record;
      });
   }
}
