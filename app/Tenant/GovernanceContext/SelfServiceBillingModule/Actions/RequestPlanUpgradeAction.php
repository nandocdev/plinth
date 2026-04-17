<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions;

use App\Central\BillingModule\Events\SubscriptionUpdated;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Actions\GetCheckoutMethodsForTenantContextAction;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\DTOs\RequestPlanUpgradeData;
use App\Tenant\GovernanceContext\SelfServiceBillingModule\Events\PlanUpgradeRequestedByTenant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RequestPlanUpgradeAction {
   public function __construct(
      private readonly GetCheckoutMethodsForTenantContextAction $checkoutMethodsAction,
   ) {
   }

   public function execute(RequestPlanUpgradeData $data): TenantSubscription {
      $methodOptions = $this->checkoutMethodsAction->execute($data->tenantId);

      /** @var array<string, array<string, mixed>> $methodMap */
      $methodMap = [];
      foreach ($methodOptions as $option) {
         $methodType = $option['method_type'] ?? null;
         if (is_string($methodType) && $methodType !== '') {
            $methodMap[$methodType] = $option;
         }
      }

      $selectedMethod = $methodMap[$data->methodType] ?? null;
      if (! is_array($selectedMethod)) {
         throw new RuntimeException('El método de pago seleccionado no está disponible para tu contexto.');
      }

      /** @var TenantSubscription $subscription */
      $subscription = DB::connection('central')->transaction(function () use ($data, $selectedMethod): TenantSubscription {
         /** @var TenantSubscription|null $subscription */
         $subscription = TenantSubscription::on('central')
            ->lockForUpdate()
            ->where('tenant_id', $data->tenantId)
            ->whereNotIn('status', [TenantSubscription::STATUS_CANCELED, TenantSubscription::STATUS_DELETED])
            ->first();

         if (! $subscription instanceof TenantSubscription) {
            throw new RuntimeException('No se encontró suscripción activa para este tenant.');
         }

         /** @var Plan $plan */
         $plan = Plan::on('central')->findOrFail($data->planId);

         if (! $plan->is_active) {
            throw new RuntimeException('El plan seleccionado no está disponible.');
         }

         $previousPlanId = (int) $subscription->plan_id;

         $newPrice = $data->billingPeriod === 'yearly'
            ? ($plan->price_yearly_cents ?? $plan->price_monthly_cents)
            : $plan->price_monthly_cents;

         $subscription->fill([
            'plan_id' => $plan->id,
            'billing_period' => $data->billingPeriod,
            'price_snapshot_cents' => $newPrice,
            'meta' => array_merge($subscription->meta ?? [], [
               'last_upgrade_at' => now()->toDateTimeString(),
               'upgraded_by' => 'tenant_self_service',
               'method_type' => (string) ($selectedMethod['method_type'] ?? 'card'),
               'provider' => (string) ($selectedMethod['provider'] ?? 'dlocal'),
               'manual_confirmation_required' => (bool) ($selectedMethod['manual_confirmation_required'] ?? false),
               'checkout_status_message' => (string) ($selectedMethod['status_message'] ?? ''),
            ]),
         ]);

         $subscription->save();

         // No llamar fresh() para preservar los atributos custom en el mismo objeto de instancia.
         $subscription->setAttribute('_previous_plan_id', $previousPlanId);
         $subscription->setAttribute('_checkout_status_message', (string) ($selectedMethod['status_message'] ?? ''));
         $subscription->setAttribute('_manual_confirmation_required', (bool) ($selectedMethod['manual_confirmation_required'] ?? false));
         $subscription->setRelation('plan', $plan);

         return $subscription;
      });

      $previousPlanId = (int) $subscription->getAttribute('_previous_plan_id');
      $previousStatus = $subscription->status;

      event(new PlanUpgradeRequestedByTenant($subscription, $previousPlanId));

      // Reutiliza listeners existentes (notificación, partner webhooks) que escuchan SubscriptionUpdated.
      event(new SubscriptionUpdated($subscription, $previousStatus));

      return $subscription;
   }
}
