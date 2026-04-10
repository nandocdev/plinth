<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SettingsModule\Actions;

use App\Tenant\GovernanceContext\SettingsModule\DTOs\TenantSettingsData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class GetTenantSettingsAction {
   public function execute(): TenantSettingsData {
      /** @var array<string, mixed> $payload */
      $payload = Cache::remember($this->cacheKey(), now()->addMinutes(15), function (): array {
         $tenantId = $this->tenantId();

         $record = DB::connection('central')
            ->table('tenant_settings')
            ->where('tenant_id', $tenantId)
            ->first();

         if (! is_object($record)) {
            $defaults = TenantSettingsData::defaults();

            DB::connection('central')
               ->table('tenant_settings')
               ->insert([
               'tenant_id' => $tenantId,
               ...$this->jsonColumnsForInsert($defaults->toPersistencePayload()),
               'created_at' => now(),
               'updated_at' => now(),
            ]);

            return $defaults->toCachePayload();
         }

         /** @var array<string, mixed> $row */
         $row = [
            'company_name' => $record->company_name,
            'legal_name' => $record->legal_name,
            'support_email' => $record->support_email,
            'locale' => $record->locale,
            'timezone' => $record->timezone,
            'currency' => $record->currency,
            'branding' => $this->decodeJsonColumn($record->branding),
            'preferences' => $this->decodeJsonColumn($record->preferences),
         ];

         return TenantSettingsData::fromRecord($row)->toCachePayload();
      });

      return TenantSettingsData::fromArray($payload);
   }

   private function cacheKey(): string {
      $tenantId = $this->tenantId();

      return 'tenant_' . $tenantId . '_workspace_settings';
   }

   private function tenantId(): string {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para settings.');
      }

      return $tenantId;
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

   /**
    * @param  array<string, mixed>  $payload
    * @return array<string, mixed>
    */
   private function jsonColumnsForInsert(array $payload): array {
      $payload['branding'] = json_encode($payload['branding'] ?? [], JSON_THROW_ON_ERROR);
      $payload['preferences'] = json_encode($payload['preferences'] ?? [], JSON_THROW_ON_ERROR);

      return $payload;
   }
}
