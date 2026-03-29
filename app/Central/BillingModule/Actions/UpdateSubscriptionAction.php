<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\DTOs\UpdateSubscriptionData;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class UpdateSubscriptionAction {
   public function execute(UpdateSubscriptionData $data): TenantSubscription {
      /** @var TenantSubscription $subscription */
      $subscription = DB::connection('central')->transaction(function () use ($data): TenantSubscription {
         /** @var TenantSubscription $subscription */
         $subscription = TenantSubscription::query()->findOrFail($data->subscriptionId);

         $tenant = Tenant::query()->find($data->tenantId);

         if (! $tenant instanceof Tenant) {
            throw new RuntimeException('Tenant no encontrado para actualizar suscripcion.');
         }

         /** @var Plan $plan */
         $plan = Plan::query()->findOrFail($data->planId);

         $subscription->fill([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_period' => $data->billingPeriod,
            'status' => $data->status,
            'trial_ends_at' => $data->trialEndsAt,
            'ends_at' => $data->endsAt,
            'price_snapshot_cents' => $data->billingPeriod === 'yearly'
               ? ($plan->price_yearly_cents ?? $plan->price_monthly_cents)
               : $plan->price_monthly_cents,
         ]);

         if ($data->status === 'canceled' && $subscription->ends_at === null) {
            $subscription->setAttribute('ends_at', CarbonImmutable::now()->toDateTimeString());
         }

         if ($data->status !== 'canceled' && $subscription->ends_at !== null && $data->endsAt === null) {
            $subscription->setAttribute('ends_at', null);
         }

         $subscription->save();

         return $subscription;
      });

      return $subscription;
   }
}
