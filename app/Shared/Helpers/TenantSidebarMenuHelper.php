<?php

declare(strict_types=1);

namespace App\Shared\Helpers;

use App\Shared\Support\Navigation\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final class TenantSidebarMenuHelper {
   /**
    * Obtiene la estructura completa del menú para el tenant actual.
    *
    * @return Collection<int, array<string, mixed>>
    */
   public static function getMenu(): Collection {
      return Menu::make(Auth::guard('tenant')->user());
   }
}
