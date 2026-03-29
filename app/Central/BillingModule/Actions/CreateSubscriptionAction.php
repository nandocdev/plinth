<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Actions;

use App\Central\BillingModule\DTOs\CreateSubscriptionData;
use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class CreateSubscriptionAction {
   public function execute(CreateSubscriptionData $data): TenantSubscription {
      /** @var TenantSubscription $subscription */
      $subscription = DB::connection('central')->transaction(function () use ($data): TenantSubscription {
         $tenant = Tenant::query()->find($data->tenantId);

         if (! $tenant instanceof Tenant) {
            throw new RuntimeException('Tenant no encontrado para crear suscripcion.');
         }

         /** @var Plan $plan */
         $plan = Plan::query()->findOrFail($data->planId);

         if (! $plan->is_active) {
            throw new RuntimeException('No puedes suscribir un tenant a un plan inactivo.');
         }

         $existing = TenantSubscription::query()
            ->where('tenant_id', $data->tenantId)
            ->exists();

         if ($existing) {
            throw new RuntimeException('El tenant ya tiene una suscripcion.');
         }

         if (! in_array($data->status, TenantSubscription::statuses(), true)) {
            throw new InvalidArgumentException('Estado de suscripcion invalido.');
         }

         if ($data->status === TenantSubscription::STATUS_DELETED) {
            throw new InvalidArgumentException('No se puede crear una suscripcion en estado deleted.');
         }

         $startsAt = CarbonImmutable::now();
         $endsAt = $data->status === TenantSubscription::STATUS_CANCELED
            ? $startsAt->toDateTimeString()
            : null;

         /** @var TenantSubscription $created */
         $created = TenantSubscription::query()->create([
            'tenant_id' => $data->tenantId,
            'plan_id' => $plan->id,
            'billing_period' => $data->billingPeriod,
            'status' => $data->status,
            'trial_ends_at' => $data->status === TenantSubscription::STATUS_TRIALING ? $data->trialEndsAt : null,
            'starts_at' => $startsAt->toDateTimeString(),
            'ends_at' => $endsAt,
            'price_snapshot_cents' => $data->billingPeriod === 'yearly'
               ? ($plan->price_yearly_cents ?? $plan->price_monthly_cents)
               : $plan->price_monthly_cents,
            'meta' => [
               'origin' => 'central-manual',
            ],
         ]);

         return $created;
      });

      event(new SubscriptionCreated($subscription));

      return $subscription;
   }
}
