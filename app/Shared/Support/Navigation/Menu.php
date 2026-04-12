<?php

declare(strict_types=1);

namespace App\Shared\Support\Navigation;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * Backward-compatible façade — delegates to MenuBuilder.
 *
 * Prefer injecting MenuBuilder directly when possible.
 * This class exists solely so existing call-sites (blade, helpers)
 * do not need to change.
 */
final class Menu {
   public static function make(?Authenticatable $user): Collection {
      return app(MenuBuilder::class)->build($user);
   }
}
