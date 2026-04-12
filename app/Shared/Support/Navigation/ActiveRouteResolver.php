<?php

declare(strict_types=1);

namespace App\Shared\Support\Navigation;

use Illuminate\Http\Request;

final class ActiveRouteResolver {
   public function __construct(private readonly Request $request) {
   }

   /**
    * Resolves whether a menu item is currently active.
    *
    * Precedence:
    *   1. item['active'] → string route pattern or callable
    *   2. item['route']  → exact route name
    *   3. any resolved child marked as active
    */
   public function resolve(array $item, array $resolvedChildren): bool {
      if (isset($item['active'])) {
         if (is_callable($item['active'])) {
            return (bool) ($item['active'])();
         }

         return $this->request->routeIs($item['active']);
      }

      if (isset($item['route'])) {
         return $this->request->routeIs($item['route']);
      }

      foreach ($resolvedChildren as $child) {
         if (! empty($child['active'])) {
            return true;
         }
      }

      return false;
   }
}
