<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Models\User;
use App\Central\ActivityLogModule\Models\ActivityLogEntry;
use App\Central\BillingModule\Models\TenantInvoice;
use App\Central\DataExportModule\Models\CentralDataExport;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use App\Central\SystemHealthModule\Actions\BuildCentralAggregateMetricsAction;
use App\Central\SystemHealthModule\DTOs\CentralAggregateMetricsData;
use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:central', 'verified', EnsureSystemAdminHasTwoFactorEnabled::class, 'central.audit'])
   ->prefix('central')
   ->name('central.')
   ->group(function (): void {
      Route::get('dashboard', function (BuildCentralAggregateMetricsAction $buildMetrics): View {
         $metrics = null;
         $recentActivity = collect();

         $openInvoices = 0;
         $openInvoicesAmountUsdCents = 0;
         $runningExports = 0;
         $failedWebhookDeliveries = 0;

         try {
            $metrics = $buildMetrics->execute();
         } catch (\Throwable) {
            $metrics = new CentralAggregateMetricsData(
               totalTenants: 0,
               activeTenants: 0,
               activeSubscriptions: 0,
               trialingSubscriptions: 0,
               pastDueSubscriptions: 0,
               monthlyPaidInvoices: 0,
               monthlyRevenueUsdCents: 0,
               monthlyRecurringRevenueUsdCents: 0,

            );
         }

         try {
            $openInvoices = TenantInvoice::query()
               ->where('status', TenantInvoice::STATUS_OPEN)
               ->count();

            $openInvoicesAmountUsdCents = (int) TenantInvoice::query()
               ->where('status', TenantInvoice::STATUS_OPEN)
               ->where('currency', 'USD')
               ->sum('amount_cents');
         } catch (\Throwable) {
            $openInvoices = 0;
            $openInvoicesAmountUsdCents = 0;
         }

         try {
            $runningExports = CentralDataExport::query()
               ->whereIn('status', [
                  CentralDataExport::STATUS_PENDING,
                  CentralDataExport::STATUS_RUNNING,
               ])
               ->count();
         } catch (\Throwable) {
            $runningExports = 0;
         }

         try {
            $failedWebhookDeliveries = PartnerWebhookDelivery::query()
               ->where('status', PartnerWebhookDelivery::STATUS_FAILED)
               ->count();
         } catch (\Throwable) {
            $failedWebhookDeliveries = 0;
         }

         try {
            $recentActivity = ActivityLogEntry::query()
               ->latest('created_at')
               ->limit(8)
               ->get();
         } catch (\Throwable) {
            $recentActivity = collect();
         }

         return view('dashboard', [
            'metrics' => $metrics,
            'openInvoices' => $openInvoices,
            'openInvoicesAmountUsdCents' => $openInvoicesAmountUsdCents,
            'runningExports' => $runningExports,
            'failedWebhookDeliveries' => $failedWebhookDeliveries,
            'recentActivity' => $recentActivity,
         ]);
      })->name('dashboard');
   });
