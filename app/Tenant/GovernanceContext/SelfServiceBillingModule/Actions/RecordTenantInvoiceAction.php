<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions;

use App\Central\BillingModule\Models\TenantInvoice;
use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Support\Str;

final class RecordTenantInvoiceAction {
   public function execute(
      string $tenantId,
      int $subscriptionId,
      int $amountCents,
      string $billingPeriod,
      string $description,
      string $status = TenantInvoice::STATUS_PAID,
      string $currency = 'USD',
   ): TenantInvoice {
      return TenantInvoice::create([
         'tenant_id' => $tenantId,
         'subscription_id' => $subscriptionId,
         'invoice_number' => 'INV-' . strtoupper((string) Str::uuid()),
         'currency' => $currency,
         'amount_cents' => $amountCents,
         'status' => $status,
         'billing_period' => $billingPeriod,
         'description' => $description,
         'paid_at' => $status === TenantInvoice::STATUS_PAID ? now()->toDateTimeString() : null,
         'meta' => [],
      ]);
   }
}
