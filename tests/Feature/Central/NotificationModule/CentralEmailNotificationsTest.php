<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Actions\CreateSubscriptionAction;
use App\Central\BillingModule\Actions\DeleteSubscriptionAction;
use App\Central\BillingModule\Actions\UpdateSubscriptionAction;
use App\Central\BillingModule\DTOs\CreateSubscriptionData;
use App\Central\BillingModule\DTOs\UpdateSubscriptionData;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\NotificationModule\Notifications\CentralEventMailNotification;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\Notification;

it('envia email a admins centrales cuando se crea un tenant', function () {
   Notification::fake();

   $verifiedA = User::factory()->create();
   $verifiedB = User::factory()->create();
   User::factory()->unverified()->create();

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-mail-one',
      'data' => ['name' => 'Tenant Mail One', 'status' => 'active'],
   ]));

   event(new TenantCreatedFromCentral($tenant));

   Notification::assertSentTo([$verifiedA, $verifiedB], CentralEventMailNotification::class);
});

it('envia emails por eventos de suscripcion en central', function () {
   Notification::fake();

   $verifiedA = User::factory()->create();
   $verifiedB = User::factory()->create();

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-subscription-mail',
      'data' => ['name' => 'Tenant Subscription Mail', 'status' => 'active'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Plan Mail Events',
      'slug' => 'plan-mail-events',
      'price_monthly_cents' => 2100,
      'price_yearly_cents' => null,
      'trial_days' => 0,
      'features' => ['api_access'],
      'max_users_soft' => null,
      'max_users_hard' => null,
      'max_storage_mb_soft' => null,
      'max_storage_mb_hard' => null,
      'is_active' => true,
      'sort_order' => 1,
   ]);

   $subscription = app(CreateSubscriptionAction::class)->execute(new CreateSubscriptionData(
      tenantId: $tenant->id,
      planId: $plan->id,
      billingPeriod: 'monthly',
      status: TenantSubscription::STATUS_ACTIVE,
      trialEndsAt: null,
   ));

   app(UpdateSubscriptionAction::class)->execute(new UpdateSubscriptionData(
      subscriptionId: $subscription->id,
      tenantId: $tenant->id,
      planId: $plan->id,
      billingPeriod: 'monthly',
      status: TenantSubscription::STATUS_PAST_DUE,
      trialEndsAt: null,
      endsAt: null,
   ));

   app(UpdateSubscriptionAction::class)->execute(new UpdateSubscriptionData(
      subscriptionId: $subscription->id,
      tenantId: $tenant->id,
      planId: $plan->id,
      billingPeriod: 'monthly',
      status: TenantSubscription::STATUS_CANCELED,
      trialEndsAt: null,
      endsAt: now()->toDateTimeString(),
   ));

   app(DeleteSubscriptionAction::class)->execute($subscription->id);

   Notification::assertSentTo([$verifiedA, $verifiedB], CentralEventMailNotification::class);
   Notification::assertCount(8);
});
