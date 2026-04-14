<?php

declare(strict_types=1);

namespace App\Central\SystemHealthModule\Actions;

use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\SystemHealthModule\DTOs\CentralAggregateMetricsData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BuildCentralAggregateMetricsAction {
   public function execute(): CentralAggregateMetricsData {
      $totalTenants = (int) DB::connection('central')->table('tenants')->count();

      $activeTenants = (int) DB::connection('central')
         ->table('tenants')
         ->whereRaw("COALESCE(data->>'status', 'active') = ?", ['active'])
         ->count();

      $newTenantsLast7Days = (int) DB::connection('central')
         ->table('tenants')
         ->where('created_at', '>=', now()->subDays(7))
         ->count();

      // Un tenant está en "riesgo" si no ha tenido uso (data->'last_usage_at') en los últimos 7 días pero tiene suscripción activa.
      $atRiskTenants = (int) DB::connection('central')
         ->table('tenants')
         ->whereRaw("COALESCE(data->>'status', 'active') = ?", ['active'])
         ->whereRaw("(data->>'last_usage_at')::timestamp < ?", [now()->subDays(7)->toDateTimeString()])
         ->count();

      $criticalErrorsLast24h = (int) DB::connection('central')
         ->table('activity_log')
         ->whereIn('log_name', ['error', 'critical', 'failed_job'])
         ->where('created_at', '>=', now()->subDay())
         ->count();

      $failedJobsCount = (int) DB::connection('central')->table('failed_jobs')->count();

      $activeSubscriptions = $this->countSubscriptionsByStatus(TenantSubscription::STATUS_ACTIVE);
      $trialingSubscriptions = $this->countSubscriptionsByStatus(TenantSubscription::STATUS_TRIALING);
      $pastDueSubscriptions = $this->countSubscriptionsByStatus(TenantSubscription::STATUS_PAST_DUE);

      $monthStart = now()->startOfMonth();
      $monthEnd = now()->endOfMonth();

      $monthlyPaidInvoices = (int) DB::connection('central')
         ->table('tenant_invoices')
         ->where('status', 'paid')
         ->whereBetween('paid_at', [$monthStart, $monthEnd])
         ->count();

      $monthlyRevenueUsdCents = (int) DB::connection('central')
         ->table('tenant_invoices')
         ->where('status', 'paid')
         ->where('currency', 'USD')
         ->whereBetween('paid_at', [$monthStart, $monthEnd])
         ->sum('amount_cents');

      $monthlyRecurringRevenueUsdCents = $this->computeMonthlyRecurringRevenueUsdCents();

      return new CentralAggregateMetricsData(
         totalTenants: $totalTenants,
         activeTenants: $activeTenants,
         newTenantsLast7Days: $newTenantsLast7Days,
         atRiskTenants: $atRiskTenants,
         criticalErrorsLast24h: $criticalErrorsLast24h,
         failedJobsCount: $failedJobsCount,
         activeSubscriptions: $activeSubscriptions,
         trialingSubscriptions: $trialingSubscriptions,
         pastDueSubscriptions: $pastDueSubscriptions,
         monthlyPaidInvoices: $monthlyPaidInvoices,
         monthlyRevenueUsdCents: (int) $monthlyRevenueUsdCents,
         monthlyRecurringRevenueUsdCents: $monthlyRecurringRevenueUsdCents,
      );
   }

   private function countSubscriptionsByStatus(string $status): int {
      return (int) DB::connection('central')
         ->table('tenant_subscriptions')
         ->where('status', $status)
         ->count();
   }

   private function computeMonthlyRecurringRevenueUsdCents(): int {
      /** @var Collection<int, object{billing_period: string, price_snapshot_cents: int}> $subscriptions */
      $subscriptions = DB::connection('central')
         ->table('tenant_subscriptions')
         ->select(['billing_period', 'price_snapshot_cents'])
         ->whereIn('status', [
            TenantSubscription::STATUS_ACTIVE,
            TenantSubscription::STATUS_TRIALING,
            TenantSubscription::STATUS_PAST_DUE,
         ])
         ->get();

      $mrr = 0;

      foreach ($subscriptions as $subscription) {
         if ($subscription->billing_period === 'yearly') {
            $mrr += (int) round(((int) $subscription->price_snapshot_cents) / 12);
            continue;
         }

         $mrr += (int) $subscription->price_snapshot_cents;
      }

      return $mrr;
   }
}
