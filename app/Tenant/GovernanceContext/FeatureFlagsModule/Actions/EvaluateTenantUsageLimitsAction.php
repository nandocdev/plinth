<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\FeatureFlagsModule\Actions;

use App\Central\BillingModule\Models\TenantSubscription;
use App\Tenant\GovernanceContext\FeatureFlagsModule\DTOs\UsageLimitsEvaluationData;
use Illuminate\Support\Facades\DB;
use stdClass;

final class EvaluateTenantUsageLimitsAction {
   public function execute(string $tenantId): UsageLimitsEvaluationData {
      /** @var stdClass|null $limits */
      $limits = DB::connection('central')
         ->table('tenant_subscriptions')
         ->join('plans', 'plans.id', '=', 'tenant_subscriptions.plan_id')
         ->where('tenant_subscriptions.tenant_id', $tenantId)
         ->whereIn('tenant_subscriptions.status', [
            TenantSubscription::STATUS_TRIALING,
            TenantSubscription::STATUS_ACTIVE,
            TenantSubscription::STATUS_PAST_DUE,
         ])
         ->select([
            'plans.max_users_soft',
            'plans.max_users_hard',
            'plans.max_storage_mb_soft',
            'plans.max_storage_mb_hard',
         ])
         ->first();

      if ($limits === null) {
         return new UsageLimitsEvaluationData(false, false, [], []);
      }

      $metadata = $this->tenantMetadataFromCentral($tenantId);
      $usersUsage = $this->usageValue($metadata, 'users');
      $storageUsage = $this->usageValue($metadata, 'storage_mb');

      $softWarnings = [];
      $hardViolations = [];

      $this->evaluateMetric(
         'users',
         $usersUsage,
         $limits->max_users_soft !== null ? (int) $limits->max_users_soft : null,
         $limits->max_users_hard !== null ? (int) $limits->max_users_hard : null,
         $softWarnings,
         $hardViolations,
      );

      $this->evaluateMetric(
         'storage_mb',
         $storageUsage,
         $limits->max_storage_mb_soft !== null ? (int) $limits->max_storage_mb_soft : null,
         $limits->max_storage_mb_hard !== null ? (int) $limits->max_storage_mb_hard : null,
         $softWarnings,
         $hardViolations,
      );

      return new UsageLimitsEvaluationData(
         softLimitReached: $softWarnings !== [],
         hardLimitReached: $hardViolations !== [],
         softWarnings: $softWarnings,
         hardViolations: $hardViolations,
      );
   }

   /**
    * @return array<string, mixed>
    */
   private function tenantMetadataFromCentral(string $tenantId): array {
      $data = DB::connection('central')
         ->table('tenants')
         ->where('id', $tenantId)
         ->value('data');

      return $this->normalizeMetadata($data);
   }

   private function normalizeMetadata(mixed $data): array {
      if (is_array($data)) {
         return $data;
      }

      if (is_object($data)) {
         return (array) $data;
      }

      if (! is_string($data)) {
         return [];
      }

      $decoded = json_decode($data, true);

      return is_array($decoded) ? $decoded : [];
   }

   /**
    * @param array<string, mixed> $metadata
    */
   private function usageValue(array $metadata, string $metric): int {
      $value = data_get($metadata, "usage.{$metric}");

      if (! is_numeric($value)) {
         return 0;
      }

      return max(0, (int) $value);
   }

   /**
    * @param list<string> $softWarnings
    * @param list<string> $hardViolations
    */
   private function evaluateMetric(
      string $metric,
      int $usage,
      ?int $softLimit,
      ?int $hardLimit,
      array &$softWarnings,
      array &$hardViolations,
   ): void {
      if ($hardLimit !== null && $usage >= $hardLimit) {
         $hardViolations[] = sprintf('%s=%d/%d', $metric, $usage, $hardLimit);

         return;
      }

      if ($softLimit !== null && $usage >= $softLimit) {
         $softWarnings[] = sprintf('%s=%d/%d', $metric, $usage, $softLimit);
      }
   }
}
