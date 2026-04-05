<?php

declare(strict_types=1);

namespace App\Tenant\AddonsModule\Actions;

use App\Tenant\AddonsModule\Enums\AvailableAddon;
use App\Tenant\AddonsModule\Models\TenantAddon;
use Illuminate\Support\Facades\DB;

/**
 * Activa o desactiva un addon ya instalado sin cambiar installed_at.
 * Si el addon no existía lo crea en estado activo (equivale a InstallAddon).
 */
final class ToggleAddonAction {
   public function execute(AvailableAddon $addon): TenantAddon {
      return DB::transaction(function () use ($addon): TenantAddon {
         /** @var TenantAddon|null $record */
         $record = TenantAddon::query()
            ->where('addon_slug', $addon->value)
            ->first();

         if ($record === null) {
            /** @var TenantAddon $record */
            $record = TenantAddon::query()->create([
               'addon_slug'   => $addon->value,
               'is_active'    => true,
               'installed_at' => now(),
            ]);

            return $record;
         }

         $nowActive = ! $record->is_active;

         $record->update([
            'is_active'      => $nowActive,
            'installed_at'   => $nowActive ? ($record->installed_at ?? now()) : $record->installed_at,
            'uninstalled_at' => $nowActive ? null : now(),
         ]);

         return $record->fresh() ?? $record;
      });
   }
}
