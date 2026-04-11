<?php

declare(strict_types=1);

namespace App\Shared\Support\Navigation;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

final class Menu {

   public static function make(?Authenticatable $user): Collection {
      $menu = config('menu', []);

      if (! is_array($menu)) {
         return collect();
      }

      return collect($menu)
         ->map(fn($section) => [
            'heading' => $section['heading'] ?? '',
            'items'   => self::filter(is_array($section['items'] ?? null) ? $section['items'] : [], $user),
         ])
         ->filter(fn($section) => !empty($section['items']))
         ->values();
   }

   private static function filter(array $items, ?Authenticatable $user): array {
      $out = [];

      foreach ($items as $item) {
         if (!self::authorized($item, $user)) {
            continue;
         }

         if (isset($item['children'])) {
            $item['children'] = self::filter($item['children'], $user);

            if (empty($item['children']) && !isset($item['route'])) {
               continue;
            }
         }

         $item['active'] = self::isActive($item);

         $out[] = $item;
      }

      return $out;
   }

   private static function authorized(array $item, ?Authenticatable $user): bool {
      if (!isset($item['can'])) {
         return (bool) $user;
      }

      // closure: fn(?Authenticatable $user): bool
      if (is_callable($item['can'])) {
         return (bool) ($item['can'])($user);
      }

      // string permission
      if (is_string($item['can'])) {
         return $user?->can($item['can']) ?? false;
      }

      // policy style
      if (is_array($item['can'])) {
         return $user?->can(
            $item['can']['ability'],
            $item['can']['model']
         ) ?? false;
      }

      return false;
   }

   private static function isActive(array $item): bool {
      if (isset($item['active'])) {
         // closure form: fn(): bool
         if (is_callable($item['active'])) {
            return (bool) ($item['active'])();
         }

         return Request::routeIs($item['active']);
      }

      if (isset($item['route'])) {
         return Request::routeIs($item['route']);
      }

      if (isset($item['children'])) {
         foreach ($item['children'] as $child) {
            if (!empty($child['active'])) {
               return true;
            }
         }
      }

      return false;
   }
}
