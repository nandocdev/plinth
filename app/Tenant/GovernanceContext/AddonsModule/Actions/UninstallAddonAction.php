<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\AddonsModule\Actions;

use App\Tenant\GovernanceContext\AddonsModule\Enums\AvailableAddon;
use App\Tenant\GovernanceContext\AddonsModule\Models\TenantAddon;
use Illuminate\Support\Facades\DB;

final class UninstallAddonAction {
   public function execute(AvailableAddon $addon): void {
      DB::transaction(function () use ($addon): void {
         TenantAddon::query()
            ->where('addon_slug', $addon->value)
            ->update([
               'is_active'      => false,
               'uninstalled_at' => now(),
            ]);
      });
   }
}
