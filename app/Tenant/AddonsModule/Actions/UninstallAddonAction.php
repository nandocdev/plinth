<?php

declare(strict_types=1);

namespace App\Tenant\AddonsModule\Actions;

use App\Tenant\AddonsModule\Enums\AvailableAddon;
use App\Tenant\AddonsModule\Models\TenantAddon;
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
