<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\AddonsModule\DTOs;

use App\Tenant\GovernanceContext\AddonsModule\Enums\AddonCategory;
use App\Tenant\GovernanceContext\AddonsModule\Enums\AvailableAddon;

/**
 * Estado combinado de un addon: definición del catálogo + estado del tenant.
 */
final readonly class AddonStateData {
   public function __construct(
      public AvailableAddon $addon,
      public string         $slug,
      public string         $label,
      public string         $description,
      public string         $icon,
      public AddonCategory  $category,
      public bool           $isInstalled,
      public bool           $isActive,
      public ?string        $installedAt,
      public ?string        $uninstalledAt,
   ) {
   }

   public static function fromCatalog(AvailableAddon $addon, bool $installed, bool $active, ?string $installedAt, ?string $uninstalledAt): self {
      return new self(
         addon: $addon,
         slug: $addon->value,
         label: $addon->label(),
         description: $addon->description(),
         icon: $addon->icon(),
         category: $addon->category(),
         isInstalled: $installed,
         isActive: $active,
         installedAt: $installedAt,
         uninstalledAt: $uninstalledAt,
      );
   }
}
