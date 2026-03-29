<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Actions;

use App\Central\ActivityLogModule\Models\ActivityLogEntry;
use App\Central\BillingModule\Models\TenantInvoice;
use App\Central\DataExportModule\DTOs\CentralDataExportPayloadData;
use App\Central\DataExportModule\Models\CentralDataExport;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantRecoverySnapshot;

final class BuildCentralDataExportPayloadAction {
   public function execute(CentralDataExport $export): CentralDataExportPayloadData {
      /** @var Tenant $tenant */
      $tenant = Tenant::query()
         ->with(['domains', 'subscription.plan'])
         ->findOrFail($export->tenant_id);

      $invoices = TenantInvoice::query()
         ->where('tenant_id', $tenant->id)
         ->orderBy('created_at')
         ->get()
         ->map(fn(TenantInvoice $invoice): array => [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'currency' => $invoice->currency,
            'amount_cents' => $invoice->amount_cents,
            'billing_period' => $invoice->billing_period,
            'description' => $invoice->description,
            'paid_at' => $invoice->paid_at?->toIso8601String(),
            'meta' => is_array($invoice->meta) ? $invoice->meta : [],
            'created_at' => $invoice->created_at?->toIso8601String(),
         ])
         ->values()
         ->all();

      $activityLog = [];

      if ($export->include_activity_log) {
         $activityLog = ActivityLogEntry::query()
            ->with('causer')
            ->where('properties->tenant_id', $tenant->id)
            ->latest('id')
            ->get()
            ->map(function (ActivityLogEntry $entry): array {
               return [
                  'id' => $entry->id,
                  'log_name' => $entry->log_name,
                  'event' => $entry->event,
                  'description' => $entry->description,
                  'subject_type' => $entry->subject_type,
                  'subject_id' => $entry->subject_id,
                  'causer_type' => $entry->causer_type,
                  'causer_id' => $entry->causer_id,
                  'properties' => is_array($entry->properties) ? $entry->properties : [],
                  'created_at' => $entry->created_at?->toIso8601String(),
               ];
            })
            ->values()
            ->all();
      }

      $recoverySnapshots = TenantRecoverySnapshot::query()
         ->where('tenant_id', $tenant->id)
         ->orderByDesc('id')
         ->get()
         ->map(fn(TenantRecoverySnapshot $snapshot): array => [
            'id' => $snapshot->id,
            'operation' => $snapshot->operation,
            'status' => $snapshot->status,
            'database_dump_path' => $snapshot->database_dump_path,
            'storage_archive_path' => $snapshot->storage_archive_path,
            'manifest_path' => $snapshot->manifest_path,
            'started_at' => $snapshot->started_at?->toIso8601String(),
            'completed_at' => $snapshot->completed_at?->toIso8601String(),
            'error_message' => $snapshot->error_message,
            'meta' => is_array($snapshot->meta) ? $snapshot->meta : [],
            'created_at' => $snapshot->created_at?->toIso8601String(),
         ])
         ->values()
         ->all();

      $subscription = $tenant->subscription;

      return new CentralDataExportPayloadData(
         tenant: [
            'id' => $tenant->id,
            'name' => $tenant->displayName(),
            'status' => $tenant->status(),
            'region' => $tenant->region(),
            'branding' => $tenant->branding(),
            'created_at' => $tenant->created_at?->toIso8601String(),
            'updated_at' => $tenant->updated_at?->toIso8601String(),
         ],
         domains: $tenant->domains->map(fn($domain): array => [
            'id' => $domain->id,
            'domain' => $domain->domain,
            'verified_at' => $domain->verified_at?->toIso8601String(),
            'created_at' => $domain->created_at?->toIso8601String(),
         ])->values()->all(),
         billing: [
            'subscription' => $subscription === null ? null : [
               'id' => $subscription->id,
               'plan_id' => $subscription->plan_id,
               'plan_name' => $subscription->plan?->name,
               'billing_period' => $subscription->billing_period,
               'status' => $subscription->status,
               'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
               'starts_at' => $subscription->starts_at?->toIso8601String(),
               'ends_at' => $subscription->ends_at?->toIso8601String(),
               'price_snapshot_cents' => $subscription->price_snapshot_cents,
               'external_id' => $subscription->external_id,
               'meta' => is_array($subscription->meta) ? $subscription->meta : [],
               'created_at' => $subscription->created_at?->toIso8601String(),
            ],
            'invoices' => $invoices,
         ],
         activityLog: $activityLog,
         recoverySnapshots: $recoverySnapshots,
         generatedAt: now()->toIso8601String(),
      );
   }
}
