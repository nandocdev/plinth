<?php

declare(strict_types=1);

namespace App\Shared\Support\Navigation;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

final class MenuBuilder {
   public function __construct(
      private readonly AuthorizationChecker $auth,
      private readonly ActiveRouteResolver $active,
   ) {
   }

   public function build(?Authenticatable $user): Collection {
      $items = config('menu', []);

      if (! is_array($items)) {
         return collect();
      }

      $filtered = $this->getPermissionFiltered($items, $user);

      return collect($this->resolveActiveState($filtered))->values();
   }

   /**
    * Invalidate the menu cache for a specific user and tenant.
    * Call this whenever roles or permissions change.
    */
   public function invalidate(?Authenticatable $user): void {
      $tenantId = $this->resolveTenantId();
      $userId   = $user?->getAuthIdentifier() ?? 'guest';

      cache()->forget("menu:{$tenantId}:{$userId}");
   }

   /**
    * Return permission-filtered items, cached per tenant + user.
    * Active state is intentionally excluded from the cache — it is
    * resolved per-request in resolveActiveState().
    */
   private function getPermissionFiltered(array $items, ?Authenticatable $user): array {
      $tenantId = $this->resolveTenantId();
      $userId   = $user?->getAuthIdentifier() ?? 'guest';
      $cacheKey = "menu:{$tenantId}:{$userId}";

      return cache()->remember(
         $cacheKey,
         300,
         fn(): array => $this->filterByPermission($items, $user),
      );
   }

   private function filterByPermission(array $items, ?Authenticatable $user): array {
      $out = [];

      foreach ($items as $item) {
         if (! $this->auth->passes($item, $user)) {
            continue;
         }

         $children = isset($item['children'])
            ? $this->filterByPermission($item['children'], $user)
            : [];

         // Drop parent-only nodes whose children collapsed to nothing
         if (isset($item['children']) && empty($children) && ! isset($item['route'])) {
            continue;
         }

         $out[] = $this->sanitizeForCache($item, $children);
      }

      return $out;
   }

   /**
    * Strip keys that must not be cached:
    *   - 'can': already evaluated, contains potential closures
    *   - callable 'active': not serializable; ActiveRouteResolver handles it
    */
   private function sanitizeForCache(array $item, array $processedChildren): array {
      unset($item['can']);

      if (isset($item['active']) && is_callable($item['active'])) {
         unset($item['active']);
      }

      $item['children'] = $processedChildren;

      return $item;
   }

   /**
    * Walk the already-filtered tree and resolve each item's active state
    * from the current request. Runs every request — never cached.
    */
   private function resolveActiveState(array $items): array {
      $out = [];

      foreach ($items as $item) {
         $children = isset($item['children'])
            ? $this->resolveActiveState($item['children'])
            : [];

         $out[] = [
            ...$item,
            'children' => $children,
            'active'   => $this->active->resolve($item, $children),
         ];
      }

      return $out;
   }

   private function resolveTenantId(): string {
      return (string) (rescue(fn() => tenant()?->id, 'central', false) ?? 'central');
   }
}
