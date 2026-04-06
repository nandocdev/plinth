<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Middleware;

use App\Shared\Support\TenantPreferenceCatalog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ApplyTenantRuntimePreferences {
   public function handle(Request $request, Closure $next): Response {
      if (! function_exists('tenancy') || ! tenancy()->initialized || tenant() === null) {
         return $next($request);
      }

      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         return $next($request);
      }

      $preferences = Cache::remember(
         $this->cacheKey($tenantId),
         now()->addMinutes(15),
         fn(): array => $this->loadTenantPreferences($tenantId),
      );

      $locale = TenantPreferenceCatalog::normalizeLocale((string) ($preferences['locale'] ?? ''));
      $currency = TenantPreferenceCatalog::normalizeCurrency((string) ($preferences['currency'] ?? ''));

      app()->setLocale($locale);
      Carbon::setLocale($locale);

      config([
         'app.locale' => $locale,
         'tenant.preferences.locale' => $locale,
         'tenant.preferences.currency' => $currency,
      ]);

      return $next($request);
   }

   /**
    * @return array<string, string>
    */
   private function loadTenantPreferences(string $tenantId): array {
      $record = DB::connection('central')
         ->table('tenant_settings')
         ->select(['locale', 'currency'])
         ->where('tenant_id', $tenantId)
         ->first();

      if (! is_object($record)) {
         return [
            'locale' => TenantPreferenceCatalog::defaultLocale(),
            'currency' => TenantPreferenceCatalog::defaultCurrency(),
         ];
      }

      return [
         'locale' => (string) ($record->locale ?? TenantPreferenceCatalog::defaultLocale()),
         'currency' => (string) ($record->currency ?? TenantPreferenceCatalog::defaultCurrency()),
      ];
   }

   private function cacheKey(string $tenantId): string {
      return 'tenant_' . $tenantId . '_runtime_preferences';
   }
}
