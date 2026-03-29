<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Livewire\Forms;

use App\Central\BillingModule\Models\TenantSubscription;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class SubscriptionForm extends Form {
   public string $tenantId = '';

   public int $planId = 0;

   public string $billingPeriod = 'monthly';

   public string $status = 'trialing';

   public string $trialEndsAt = '';

   public string $endsAt = '';

   /**
    * @return array{tenantId: string, planId: int, billingPeriod: string, status: string, trialEndsAt: ?string, endsAt: ?string}
    */
   public function payload(?int $subscriptionId = null): array {
      $this->validate([
         'tenantId' => ['required', 'string', 'exists:tenants,id', Rule::unique('tenant_subscriptions', 'tenant_id')->ignore($subscriptionId)],
         'planId' => ['required', 'integer', 'exists:plans,id'],
         'billingPeriod' => ['required', 'string', Rule::in(['monthly', 'yearly'])],
         'status' => ['required', 'string', Rule::in(TenantSubscription::statuses())],
         'trialEndsAt' => ['nullable', 'date'],
         'endsAt' => ['nullable', 'date'],
      ]);

      return [
         'tenantId' => trim($this->tenantId),
         'planId' => $this->planId,
         'billingPeriod' => $this->billingPeriod,
         'status' => $this->status,
         'trialEndsAt' => $this->trialEndsAt !== '' ? $this->trialEndsAt : null,
         'endsAt' => $this->endsAt !== '' ? $this->endsAt : null,
      ];
   }

   public function fillFromSubscription(array $data): void {
      $this->tenantId = (string) ($data['tenant_id'] ?? '');
      $this->planId = (int) ($data['plan_id'] ?? 0);
      $this->billingPeriod = (string) ($data['billing_period'] ?? 'monthly');
      $this->status = (string) ($data['status'] ?? 'trialing');
      $this->trialEndsAt = isset($data['trial_ends_at']) && $data['trial_ends_at'] !== null
         ? (string) $data['trial_ends_at']
         : '';
      $this->endsAt = isset($data['ends_at']) && $data['ends_at'] !== null
         ? (string) $data['ends_at']
         : '';
   }

   public function clear(): void {
      $this->reset();
      $this->billingPeriod = 'monthly';
      $this->status = 'trialing';
   }
}
