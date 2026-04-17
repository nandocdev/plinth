<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions;

use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\DTOs\CheckoutMethodOptionData;

final class GetCheckoutMethodsForTenantContextAction {
   /**
    * @return list<array{method_type: string, provider: string, label: string, description: string, manual_confirmation_required: bool, status_message: string}>
    */
   public function execute(string $tenantId): array {
      $context = $this->resolveContextFromTenant($tenantId);

      $allowedMethods = config("checkout.allowed_methods.{$context}", []);
      if (! is_array($allowedMethods)) {
         $allowedMethods = [];
      }

      $orderedMethods = $this->orderByObservedConversion($allowedMethods, $this->resolveCountryCode($tenantId));
      $catalog = config('checkout.method_catalog', []);

      if (! is_array($catalog)) {
         $catalog = [];
      }

      $result = [];

      foreach ($orderedMethods as $methodType) {
         if (! is_string($methodType)) {
            continue;
         }

         $definition = $catalog[$methodType] ?? null;
         if (! is_array($definition)) {
            continue;
         }

         $provider = $this->resolveProviderForContext($definition, $context);

         $result[] = (new CheckoutMethodOptionData(
            methodType: $methodType,
            provider: $provider,
            label: (string) ($definition['label'] ?? strtoupper($methodType)),
            description: (string) ($definition['description'] ?? ''),
            manualConfirmationRequired: (bool) ($definition['manual_confirmation_required'] ?? false),
            statusMessage: (string) ($definition['status_message'] ?? ''),
         ))->toArray();
      }

      return $result;
   }

   private function resolveContextFromTenant(string $tenantId): string {
      $countryCode = $this->resolveCountryCode($tenantId);

      $latamCountries = config('checkout.latam_countries', []);
      if (! is_array($latamCountries)) {
         $latamCountries = [];
      }

      return in_array($countryCode, $latamCountries, true) ? 'latam' : 'global';
   }

   private function resolveCountryCode(string $tenantId): string {
      $tenantCountry = tenant('country_code');
      if (is_string($tenantCountry) && trim($tenantCountry) !== '') {
         return strtoupper(trim($tenantCountry));
      }

      $tenantCountryFallback = tenant('country');
      if (is_string($tenantCountryFallback) && trim($tenantCountryFallback) !== '') {
         return strtoupper(trim($tenantCountryFallback));
      }

      /** @var Tenant|null $tenant */
      $tenant = Tenant::on('central')->find($tenantId);

      $countryCode = null;

      if ($tenant instanceof Tenant) {
         /** @var mixed $directCountry */
         $directCountry = $tenant->getAttribute('country_code');
         if (is_string($directCountry) && trim($directCountry) !== '') {
            $countryCode = $directCountry;
         }
      }

      if (! is_string($countryCode) || trim($countryCode) === '') {
         /** @var mixed $data */
         $data = $tenant?->getAttribute('data');

         if (is_string($data) && $data !== '') {
            /** @var mixed $decoded */
            $decoded = json_decode($data, true);
            $data = $decoded;
         }

         if (is_array($data)) {
            $countryCode = $data['country_code'] ?? $data['country'] ?? null;
         }
      }

      if (! is_string($countryCode) || trim($countryCode) === '') {
         $countryCode = (string) config('checkout.default_country', 'US');
      }

      return strtoupper(trim($countryCode));
   }

   /**
    * @param list<mixed> $allowedMethods
    * @return list<string>
    */
   private function orderByObservedConversion(array $allowedMethods, string $countryCode): array {
      $orders = config('checkout.observed_method_order', []);
      if (! is_array($orders)) {
         $orders = [];
      }

      $countryOrder = $orders[$countryCode] ?? $orders['default'] ?? [];
      if (! is_array($countryOrder)) {
         $countryOrder = [];
      }

      $allowedMethodMap = [];
      foreach ($allowedMethods as $methodType) {
         if (is_string($methodType)) {
            $allowedMethodMap[$methodType] = true;
         }
      }

      $ordered = [];
      foreach ($countryOrder as $methodType) {
         if (is_string($methodType) && isset($allowedMethodMap[$methodType])) {
            $ordered[] = $methodType;
            unset($allowedMethodMap[$methodType]);
         }
      }

      foreach (array_keys($allowedMethodMap) as $methodType) {
         $ordered[] = $methodType;
      }

      return $ordered;
   }

   /**
    * @param array<string, mixed> $definition
    */
   private function resolveProviderForContext(array $definition, string $context): string {
      $providers = $definition['provider_by_context'] ?? [];
      if (! is_array($providers)) {
         return 'dlocal';
      }

      $provider = $providers[$context] ?? $providers['global'] ?? 'dlocal';

      return is_string($provider) && $provider !== '' ? $provider : 'dlocal';
   }
}
