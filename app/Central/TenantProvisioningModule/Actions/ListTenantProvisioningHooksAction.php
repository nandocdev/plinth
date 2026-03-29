<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\TenantProvisioningHookData;
use App\Central\TenantProvisioningModule\Models\Tenant;

final class ListTenantProvisioningHooksAction {
   /**
    * @return list<TenantProvisioningHookData>
    */
   public function execute(Tenant $tenant): array {
      $configuredHooks = config('tenant_provisioning.hooks', []);

      if (! is_array($configuredHooks) || $configuredHooks === []) {
         return [];
      }

      $placeholders = $this->placeholders($tenant);
      $hooks = [];

      foreach ($configuredHooks as $name => $definition) {
         if (! is_string($name) || ! is_array($definition) || ! ($definition['enabled'] ?? false)) {
            continue;
         }

         $command = $this->interpolateList($definition['command'] ?? [], $placeholders);

         if ($command === []) {
            continue;
         }

         $workingDirectory = $definition['working_directory'] ?? null;

         $hooks[] = new TenantProvisioningHookData(
            name: $name,
            driver: (string) ($definition['driver'] ?? $name),
            command: $command,
            workingDirectory: is_string($workingDirectory) && $workingDirectory !== ''
               ? $this->interpolateValue($workingDirectory, $placeholders)
               : null,
            timeout: max(1, (int) ($definition['timeout'] ?? 900)),
            environment: $this->interpolateMap($definition['environment'] ?? [], $placeholders),
         );
      }

      return $hooks;
   }

   /**
    * @return array<string, string>
    */
   private function placeholders(Tenant $tenant): array {
      $primaryDomain = (string) $tenant->domains()->orderBy('id')->value('domain');
      $databaseName = (string) ($tenant->getAttribute('tenancy_db_name') ?? '');
      $dbConnection = (string) ($tenant->getAttribute('tenancy_db_connection') ?? config('tenancy.database.template_tenant_connection', 'tenant_template'));
      $context = [
         'tenant_id' => (string) $tenant->id,
         'tenant_name' => $tenant->displayName(),
         'tenant_domain' => $primaryDomain,
         'tenant_region' => $tenant->region(),
         'tenant_database' => $databaseName,
         'tenant_db_connection' => $dbConnection,
      ];

      return [
         '{tenant_id}' => $context['tenant_id'],
         '{tenant_name}' => $context['tenant_name'],
         '{tenant_domain}' => $context['tenant_domain'],
         '{tenant_region}' => $context['tenant_region'],
         '{tenant_database}' => $context['tenant_database'],
         '{tenant_db_connection}' => $context['tenant_db_connection'],
         '{tenant_context_json}' => (string) json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
      ];
   }

   /**
    * @param mixed $values
    * @param array<string, string> $placeholders
    * @return list<string>
    */
   private function interpolateList(mixed $values, array $placeholders): array {
      if (! is_array($values)) {
         return [];
      }

      $resolved = [];

      foreach ($values as $value) {
         if (! is_string($value) || $value === '') {
            continue;
         }

         $resolved[] = $this->interpolateValue($value, $placeholders);
      }

      return $resolved;
   }

   /**
    * @param mixed $values
    * @param array<string, string> $placeholders
    * @return array<string, string>
    */
   private function interpolateMap(mixed $values, array $placeholders): array {
      if (! is_array($values)) {
         return [];
      }

      $resolved = [];

      foreach ($values as $key => $value) {
         if (! is_string($key) || ! is_string($value) || $key === '') {
            continue;
         }

         $resolved[$key] = $this->interpolateValue($value, $placeholders);
      }

      return $resolved;
   }

   /**
    * @param array<string, string> $placeholders
    */
   private function interpolateValue(string $value, array $placeholders): string {
      return strtr($value, $placeholders);
   }
}
