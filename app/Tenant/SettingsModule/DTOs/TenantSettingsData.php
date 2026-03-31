<?php

declare(strict_types=1);

namespace App\Tenant\SettingsModule\DTOs;

use App\Tenant\SettingsModule\Models\TenantSetting;

final readonly class TenantSettingsData {
   public function __construct(
      public string  $companyName,
      public ?string $legalName,
      public ?string $supportEmail,
      public ?string $brandName,
      public ?string $logoUrl,
      public string  $primaryColor,
      public string  $secondaryColor,
      public string  $locale,
      public string  $timezone,
      public string  $currency,
      public bool    $allowWeeklyDigest,
      public string  $dateFormat,
   ) {}

   public static function defaults(): self {
      return new self(
         companyName: 'Mi Empresa',
         legalName: null,
         supportEmail: null,
         brandName: null,
         logoUrl: null,
         primaryColor: '#0f172a',
         secondaryColor: '#2563eb',
         locale: 'es',
         timezone: 'UTC',
         currency: 'USD',
         allowWeeklyDigest: true,
         dateFormat: 'd/m/Y',
      );
   }

   public static function fromModel(TenantSetting $settings): self {
      $branding = is_array($settings->branding) ? $settings->branding : [];
      $preferences = is_array($settings->preferences) ? $settings->preferences : [];

      return new self(
         companyName: (string) $settings->company_name,
         legalName: isset($settings->legal_name) && $settings->legal_name !== '' ? (string) $settings->legal_name : null,
         supportEmail: isset($settings->support_email) && $settings->support_email !== '' ? (string) $settings->support_email : null,
         brandName: isset($branding['brand_name']) && $branding['brand_name'] !== '' ? (string) $branding['brand_name'] : null,
         logoUrl: isset($branding['logo_url']) && $branding['logo_url'] !== '' ? (string) $branding['logo_url'] : null,
         primaryColor: (string) ($branding['primary_color'] ?? '#0f172a'),
         secondaryColor: (string) ($branding['secondary_color'] ?? '#2563eb'),
         locale: (string) ($settings->locale ?? 'es'),
         timezone: (string) ($settings->timezone ?? 'UTC'),
         currency: (string) ($settings->currency ?? 'USD'),
         allowWeeklyDigest: (bool) ($preferences['allow_weekly_digest'] ?? true),
         dateFormat: (string) ($preferences['date_format'] ?? 'd/m/Y'),
      );
   }

   /**
    * @param  array<string, mixed>  $record
    */
   public static function fromRecord(array $record): self {
      $branding = $record['branding'] ?? [];
      $preferences = $record['preferences'] ?? [];

      return new self(
         companyName: (string) ($record['company_name'] ?? 'Mi Empresa'),
         legalName: isset($record['legal_name']) && $record['legal_name'] !== '' ? (string) $record['legal_name'] : null,
         supportEmail: isset($record['support_email']) && $record['support_email'] !== '' ? (string) $record['support_email'] : null,
         brandName: isset($branding['brand_name']) && $branding['brand_name'] !== '' ? (string) $branding['brand_name'] : null,
         logoUrl: isset($branding['logo_url']) && $branding['logo_url'] !== '' ? (string) $branding['logo_url'] : null,
         primaryColor: (string) ($branding['primary_color'] ?? '#0f172a'),
         secondaryColor: (string) ($branding['secondary_color'] ?? '#2563eb'),
         locale: (string) ($record['locale'] ?? 'es'),
         timezone: (string) ($record['timezone'] ?? 'UTC'),
         currency: strtoupper((string) ($record['currency'] ?? 'USD')),
         allowWeeklyDigest: (bool) ($preferences['allow_weekly_digest'] ?? true),
         dateFormat: (string) ($preferences['date_format'] ?? 'd/m/Y'),
      );
   }

   /**
    * @param  array<string, mixed>  $payload
    */
   public static function fromArray(array $payload): self {
      return new self(
         companyName: (string) ($payload['company_name'] ?? 'Mi Empresa'),
         legalName: isset($payload['legal_name']) && $payload['legal_name'] !== '' ? (string) $payload['legal_name'] : null,
         supportEmail: isset($payload['support_email']) && $payload['support_email'] !== '' ? (string) $payload['support_email'] : null,
         brandName: isset($payload['brand_name']) && $payload['brand_name'] !== '' ? (string) $payload['brand_name'] : null,
         logoUrl: isset($payload['logo_url']) && $payload['logo_url'] !== '' ? (string) $payload['logo_url'] : null,
         primaryColor: (string) ($payload['primary_color'] ?? '#0f172a'),
         secondaryColor: (string) ($payload['secondary_color'] ?? '#2563eb'),
         locale: (string) ($payload['locale'] ?? 'es'),
         timezone: (string) ($payload['timezone'] ?? 'UTC'),
         currency: strtoupper((string) ($payload['currency'] ?? 'USD')),
         allowWeeklyDigest: (bool) ($payload['allow_weekly_digest'] ?? true),
         dateFormat: (string) ($payload['date_format'] ?? 'd/m/Y'),
      );
   }

   /**
    * @return array<string, mixed>
    */
   public function toPersistencePayload(): array {
      return [
         'company_name' => $this->companyName,
         'legal_name' => $this->legalName,
         'support_email' => $this->supportEmail,
         'locale' => $this->locale,
         'timezone' => $this->timezone,
         'currency' => strtoupper($this->currency),
         'branding' => [
            'brand_name' => $this->brandName,
            'logo_url' => $this->logoUrl,
            'primary_color' => $this->primaryColor,
            'secondary_color' => $this->secondaryColor,
         ],
         'preferences' => [
            'allow_weekly_digest' => $this->allowWeeklyDigest,
            'date_format' => $this->dateFormat,
         ],
      ];
   }

   /**
    * @return array<string, mixed>
    */
   public function toCachePayload(): array {
      return [
         'company_name' => $this->companyName,
         'legal_name' => $this->legalName,
         'support_email' => $this->supportEmail,
         'brand_name' => $this->brandName,
         'logo_url' => $this->logoUrl,
         'primary_color' => $this->primaryColor,
         'secondary_color' => $this->secondaryColor,
         'locale' => $this->locale,
         'timezone' => $this->timezone,
         'currency' => strtoupper($this->currency),
         'allow_weekly_digest' => $this->allowWeeklyDigest,
         'date_format' => $this->dateFormat,
      ];
   }
}
