<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SettingsModule\Livewire\Forms;

use App\Shared\Support\TenantPreferenceCatalog;
use App\Tenant\GovernanceContext\SettingsModule\DTOs\TenantSettingsData;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class TenantSettingsForm extends Form {
   public string $companyName = '';
   public string $legalName = '';
   public string $supportEmail = '';
   public string $brandName = '';
   public string $logoUrl = '';
   public string $primaryColor = '#0f172a';
   public string $secondaryColor = '#2563eb';
   public string $locale = 'es';
   public string $timezone = 'UTC';
   public string $currency = 'USD';
   public bool $allowWeeklyDigest = true;
   public string $dateFormat = 'd/m/Y';

   public function fillFromData(TenantSettingsData $data): void {
      $this->companyName = $data->companyName;
      $this->legalName = $data->legalName ?? '';
      $this->supportEmail = $data->supportEmail ?? '';
      $this->brandName = $data->brandName ?? '';
      $this->logoUrl = $data->logoUrl ?? '';
      $this->primaryColor = $data->primaryColor;
      $this->secondaryColor = $data->secondaryColor;
      $this->locale = TenantPreferenceCatalog::normalizeLocale($data->locale);
      $this->timezone = $data->timezone;
      $this->currency = TenantPreferenceCatalog::normalizeCurrency($data->currency);
      $this->allowWeeklyDigest = $data->allowWeeklyDigest;
      $this->dateFormat = $data->dateFormat;
   }

   public function toData(): TenantSettingsData {
      return TenantSettingsData::fromArray([
         'company_name' => trim($this->companyName),
         'legal_name' => trim($this->legalName),
         'support_email' => trim($this->supportEmail),
         'brand_name' => trim($this->brandName),
         'logo_url' => trim($this->logoUrl),
         'primary_color' => trim($this->primaryColor),
         'secondary_color' => trim($this->secondaryColor),
         'locale' => TenantPreferenceCatalog::normalizeLocale($this->locale),
         'timezone' => trim($this->timezone),
         'currency' => TenantPreferenceCatalog::normalizeCurrency($this->currency),
         'allow_weekly_digest' => $this->allowWeeklyDigest,
         'date_format' => trim($this->dateFormat),
      ]);
   }

   /**
    * @return array<string, string>
    */
   public function localeOptions(): array {
      return TenantPreferenceCatalog::locales();
   }

   /**
    * @return array<string, string>
    */
   public function currencyOptions(): array {
      return TenantPreferenceCatalog::currencies();
   }

   /**
    * @return array<string, list<string>>
    */
   public function rules(): array {
      return [
         'companyName' => ['required', 'string', 'max:150'],
         'legalName' => ['nullable', 'string', 'max:150'],
         'supportEmail' => ['nullable', 'email', 'max:150'],
         'brandName' => ['nullable', 'string', 'max:150'],
         'logoUrl' => ['nullable', 'url', 'max:2048'],
         'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
         'secondaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
         'locale' => ['required', Rule::in(array_keys($this->localeOptions()))],
         'timezone' => ['required', 'timezone'],
         'currency' => ['required', Rule::in(array_keys($this->currencyOptions()))],
         'allowWeeklyDigest' => ['required', 'boolean'],
         'dateFormat' => ['required', 'in:d/m/Y,m/d/Y,Y-m-d'],
      ];
   }
}
