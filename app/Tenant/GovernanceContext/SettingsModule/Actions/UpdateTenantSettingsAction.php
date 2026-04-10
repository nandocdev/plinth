<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SettingsModule\Actions;

use App\Tenant\GovernanceContext\SettingsModule\DTOs\TenantSettingsData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UpdateTenantSettingsAction {
   public function execute(TenantSettingsData $data): TenantSettingsData {
      $updated = DB::transaction(function () use ($data): TenantSettingsData {
         $tenantId = $this->tenantId();
         $payload = $this->jsonColumnsForPersistence($data->toPersistencePayload());
         $table = DB::connection('central')->table('tenant_settings');

         $exists = $table->where('tenant_id', $tenantId)->exists();

         if ($exists) {
            $table->where('tenant_id', $tenantId)->update([
               ...$payload,
               'updated_at' => now(),
            ]);
         } else {
            $table->insert([
               'tenant_id' => $tenantId,
               ...$payload,
               'created_at' => now(),
               'updated_at' => now(),
            ]);
         }

         $record = DB::connection('central')
            ->table('tenant_settings')
            ->where('tenant_id', $tenantId)
            ->first();

         if (! is_object($record)) {
            throw new \RuntimeException('No fue posible persistir settings del tenant.');
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

         return TenantSettingsData::fromRecord($row);
      });

      Cache::forget($this->cacheKey());
      Cache::forget($this->runtimeCacheKey());
      Cache::put($this->cacheKey(), $updated->toCachePayload(), now()->addMinutes(15));

      return $updated;
   }

   private function cacheKey(): string {
      $tenantId = $this->tenantId();

      return 'tenant_' . $tenantId . '_workspace_settings';
   }

   private function runtimeCacheKey(): string {
      $tenantId = $this->tenantId();

      return 'tenant_' . $tenantId . '_runtime_preferences';
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
   private function jsonColumnsForPersistence(array $payload): array {
      $payload['branding'] = json_encode($payload['branding'] ?? [], JSON_THROW_ON_ERROR);
      $payload['preferences'] = json_encode($payload['preferences'] ?? [], JSON_THROW_ON_ERROR);

      return $payload;
   }
}
