<?php

declare(strict_types=1);

namespace App\Shared\Support;

final class TenantPreferenceCatalog {
   /**
    * @return array<string, string>
    */
   public static function locales(): array {
      return [
         'es' => 'Espanol',
         'en' => 'English',
         'pt_BR' => 'Portugues (Brasil)',
      ];
   }

   /**
    * @return array<string, string>
    */
   public static function currencies(): array {
      return [
         'USD' => 'Dolar estadounidense (USD)',
         'EUR' => 'Euro (EUR)',
         'MXN' => 'Peso mexicano (MXN)',
         'COP' => 'Peso colombiano (COP)',
         'CLP' => 'Peso chileno (CLP)',
         'ARS' => 'Peso argentino (ARS)',
         'BRL' => 'Real brasileno (BRL)',
         'PEN' => 'Sol peruano (PEN)',
      ];
   }

   public static function defaultLocale(): string {
      return 'es';
   }

   public static function defaultCurrency(): string {
      return 'USD';
   }

   public static function normalizeLocale(string $locale): string {
      $locale = trim($locale);

      return array_key_exists($locale, self::locales())
         ? $locale
         : self::defaultLocale();
   }

   public static function normalizeCurrency(string $currency): string {
      $currency = strtoupper(trim($currency));

      return array_key_exists($currency, self::currencies())
         ? $currency
         : self::defaultCurrency();
   }
}
