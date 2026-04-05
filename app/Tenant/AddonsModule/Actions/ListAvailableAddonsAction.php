<?php

declare(strict_types=1);

namespace App\Tenant\AddonsModule\Actions;

use App\Tenant\AddonsModule\DTOs\AddonStateData;
use App\Tenant\AddonsModule\Enums\AvailableAddon;
use App\Tenant\AddonsModule\Models\TenantAddon;
use Illuminate\Support\Collection;

final class ListAvailableAddonsAction {
   /**
    * Devuelve el catálogo completo con el estado instalado del tenant.
    *
    * @return list<AddonStateData>
    */
   public function execute(): array {
      /** @var Collection<string, TenantAddon> $installed */
      $installed = TenantAddon::query()
         ->get(['addon_slug', 'is_active', 'installed_at', 'uninstalled_at'])
         ->keyBy('addon_slug');

      $result = [];

      foreach (AvailableAddon::cases() as $addon) {
         /** @var TenantAddon|null $record */
         $record = $installed->get($addon->value);

         $result[] = AddonStateData::fromCatalog(
            addon: $addon,
            installed: $record !== null,
            active: (bool) $record?->is_active,
            installedAt: $record?->installed_at?->toDateTimeString(),
            uninstalledAt: $record?->uninstalled_at?->toDateTimeString(),
         );
      }

      return $result;
   }
}
