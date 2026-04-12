<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Middleware;

use App\Shared\Support\TenantPreferenceCatalog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
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
      $timezone = (string) ($preferences['timezone'] ?? 'UTC');
      $companyName = (string) ($preferences['company_name'] ?? 'Mi Empresa');
      $legalName = (string) ($preferences['legal_name'] ?? '');
      $supportEmail = (string) ($preferences['support_email'] ?? '');
      $brandName = (string) ($preferences['brand_name'] ?? '');
      $logoUrl = (string) ($preferences['logo_url'] ?? '');
      $primaryColor = (string) ($preferences['primary_color'] ?? '#0f172a');
      $secondaryColor = (string) ($preferences['secondary_color'] ?? '#2563eb');
      $dateFormat = (string) ($preferences['date_format'] ?? 'd/m/Y');
      $allowWeeklyDigest = (bool) ($preferences['allow_weekly_digest'] ?? true);

      app()->setLocale($locale);
      Carbon::setLocale($locale);

      config([
         'app.locale' => $locale,
         'tenant.preferences.locale' => $locale,
         'tenant.preferences.timezone' => $timezone,
         'tenant.preferences.currency' => $currency,
         'tenant.preferences.date_format' => $dateFormat,
         'tenant.preferences.allow_weekly_digest' => $allowWeeklyDigest,
         'tenant.company.name' => $companyName,
         'tenant.company.legal_name' => $legalName,
         'tenant.company.support_email' => $supportEmail,
         'tenant.brand.name' => $brandName,
         'tenant.brand.logo_url' => $logoUrl,
         'tenant.brand.primary_color' => $primaryColor,
         'tenant.brand.secondary_color' => $secondaryColor,
      ]);

      $settingsForViews = [
         'companyName' => $companyName,
         'legalName' => $legalName,
         'supportEmail' => $supportEmail,
         'brandName' => $brandName,
         'logoUrl' => $logoUrl,
         'primaryColor' => $primaryColor,
         'secondaryColor' => $secondaryColor,
         'locale' => $locale,
         'timezone' => $timezone,
         'currency' => $currency,
         'dateFormat' => $dateFormat,
         'allowWeeklyDigest' => $allowWeeklyDigest,
      ];

      View::share('tenantSettings', $settingsForViews);
      View::share('tenantBrandName', $brandName !== '' ? $brandName : $companyName);
      View::share('tenantPrimaryColor', $primaryColor);
      View::share('tenantSecondaryColor', $secondaryColor);
      View::share('tenantLogoUrl', $logoUrl);

      return $next($request);
   }

   /**
    * @return array<string, mixed>
    */
   private function loadTenantPreferences(string $tenantId): array {
      $record = DB::connection('central')
         ->table('tenant_settings')
         ->select(['company_name', 'legal_name', 'support_email', 'locale', 'timezone', 'currency', 'branding', 'preferences'])
         ->where('tenant_id', $tenantId)
         ->first();

      if (! is_object($record)) {
         return [
            'company_name' => 'Mi Empresa',
            'legal_name' => '',
            'support_email' => '',
            'locale' => TenantPreferenceCatalog::defaultLocale(),
            'timezone' => 'UTC',
            'currency' => TenantPreferenceCatalog::defaultCurrency(),
            'brand_name' => '',
            'logo_url' => '',
            'primary_color' => '#0f172a',
            'secondary_color' => '#2563eb',
            'allow_weekly_digest' => true,
            'date_format' => 'd/m/Y',
         ];
      }

      $branding = $this->decodeJsonColumn($record->branding ?? null);
      $preferences = $this->decodeJsonColumn($record->preferences ?? null);

      return [
         'company_name' => (string) ($record->company_name ?? 'Mi Empresa'),
         'legal_name' => (string) ($record->legal_name ?? ''),
         'support_email' => (string) ($record->support_email ?? ''),
         'locale' => (string) ($record->locale ?? TenantPreferenceCatalog::defaultLocale()),
         'timezone' => (string) ($record->timezone ?? 'UTC'),
         'currency' => (string) ($record->currency ?? TenantPreferenceCatalog::defaultCurrency()),
         'brand_name' => (string) ($branding['brand_name'] ?? ''),
         'logo_url' => (string) ($branding['logo_url'] ?? ''),
         'primary_color' => (string) ($branding['primary_color'] ?? '#0f172a'),
         'secondary_color' => (string) ($branding['secondary_color'] ?? '#2563eb'),
         'allow_weekly_digest' => (bool) ($preferences['allow_weekly_digest'] ?? true),
         'date_format' => (string) ($preferences['date_format'] ?? 'd/m/Y'),
      ];
   }

   /**
    * @param  mixed  $value
    * @return array<string, mixed>
    */
   private function decodeJsonColumn(mixed $value): array {
      if (is_array($value)) {
         return $value;
      }

      if (! is_string($value) || $value === '') {
         return [];
      }

      /** @var mixed $decoded */
      $decoded = json_decode($value, true);

      return is_array($decoded) ? $decoded : [];
   }

   private function cacheKey(string $tenantId): string {
      return 'tenant_' . $tenantId . '_runtime_preferences';
   }
}
