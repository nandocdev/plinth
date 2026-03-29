<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Models\Plan;
use App\Central\BillingModule\Models\TenantSubscription;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\DB;

function dlocalPayload(): array {
   return [
      'id' => 'evt_dlocal_001',
      'status' => 'approved',
      'data' => [
         'subscription_id' => 'sub_dlocal_001',
      ],
   ];
}

test('rechaza webhook dlocal con firma invalida', function () {
   config()->set('services.dlocal.webhook_secret', 'secret-test');

   $payload = dlocalPayload();
   $rawPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

   $response = $this->call(
      'POST',
      '/billing/webhooks/dlocal',
      [],
      [],
      [],
      [
         'CONTENT_TYPE' => 'application/json',
         'HTTP_X_DLOCAL_SIGNATURE' => 'firma_invalida',
      ],
      $rawPayload,
   );

   $response->assertStatus(401);

   $processedCount = DB::connection('central')->table('processed_webhooks')->count();
   expect($processedCount)->toBe(0);
});

test('procesa webhook dlocal y actualiza suscripcion por external_id', function () {
   config()->set('services.dlocal.webhook_secret', 'secret-test');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-dlocal-1',
      'data' => ['name' => 'Tenant Dlocal'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Plan Dlocal',
      'slug' => 'plan-dlocal',
      'price_monthly_cents' => 1000,
      'price_yearly_cents' => 10000,
      'trial_days' => 7,
      'features' => ['api_access'],
      'is_active' => true,
      'sort_order' => 1,
   ]);

   $subscription = TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => 'trialing',
      'starts_at' => now()->toDateTimeString(),
      'price_snapshot_cents' => 1000,
      'external_id' => 'sub_dlocal_001',
      'meta' => [],
   ]);

   $payload = dlocalPayload();
   $rawPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
   $signature = hash_hmac('sha256', (string) $rawPayload, 'secret-test');

   $response = $this->call(
      'POST',
      '/billing/webhooks/dlocal',
      [],
      [],
      [],
      [
         'CONTENT_TYPE' => 'application/json',
         'HTTP_X_DLOCAL_SIGNATURE' => $signature,
      ],
      $rawPayload,
   );

   $response->assertOk();
   $response->assertJson([
      'processed' => true,
      'duplicate' => false,
      'subscription_updated' => true,
   ]);

   $subscription->refresh();

   expect($subscription->status)->toBe('active')
      ->and($subscription->meta)->toHaveKey('dlocal_last_webhook_id', 'evt_dlocal_001');

   $processedCount = DB::connection('central')
      ->table('processed_webhooks')
      ->where('provider', 'dlocal')
      ->where('event_id', 'evt_dlocal_001')
      ->count();

   expect($processedCount)->toBe(1);
});

test('webhook dlocal es idempotente para mismo event_id', function () {
   config()->set('services.dlocal.webhook_secret', 'secret-test');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-dlocal-2',
      'data' => ['name' => 'Tenant Dlocal 2'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Plan Dlocal 2',
      'slug' => 'plan-dlocal-2',
      'price_monthly_cents' => 2000,
      'price_yearly_cents' => 20000,
      'trial_days' => 14,
      'features' => ['priority_support'],
      'is_active' => true,
      'sort_order' => 2,
   ]);

   TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => 'active',
      'starts_at' => now()->toDateTimeString(),
      'price_snapshot_cents' => 2000,
      'external_id' => 'sub_dlocal_001',
      'meta' => [],
   ]);

   $payload = dlocalPayload();
   $rawPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
   $signature = hash_hmac('sha256', (string) $rawPayload, 'secret-test');

   $first = $this->call(
      'POST',
      '/billing/webhooks/dlocal',
      [],
      [],
      [],
      [
         'CONTENT_TYPE' => 'application/json',
         'HTTP_X_DLOCAL_SIGNATURE' => $signature,
      ],
      $rawPayload,
   );

   $second = $this->call(
      'POST',
      '/billing/webhooks/dlocal',
      [],
      [],
      [],
      [
         'CONTENT_TYPE' => 'application/json',
         'HTTP_X_DLOCAL_SIGNATURE' => $signature,
      ],
      $rawPayload,
   );

   $first->assertOk();
   $second->assertOk()->assertJson([
      'processed' => true,
      'duplicate' => true,
      'subscription_updated' => false,
   ]);

   $processedCount = DB::connection('central')
      ->table('processed_webhooks')
      ->where('provider', 'dlocal')
      ->where('event_id', 'evt_dlocal_001')
      ->count();

   expect($processedCount)->toBe(1);
});

test('webhook dlocal fuera de orden no pisa estado mas reciente', function () {
   config()->set('services.dlocal.webhook_secret', 'secret-test');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-dlocal-3',
      'data' => ['name' => 'Tenant Dlocal 3'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Plan Dlocal 3',
      'slug' => 'plan-dlocal-3',
      'price_monthly_cents' => 3000,
      'price_yearly_cents' => 30000,
      'trial_days' => 14,
      'features' => ['priority_support'],
      'is_active' => true,
      'sort_order' => 3,
   ]);

   $subscription = TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => 'active',
      'starts_at' => now()->toDateTimeString(),
      'price_snapshot_cents' => 3000,
      'external_id' => 'sub_dlocal_003',
      'meta' => [],
   ]);

   $newPayload = [
      'id' => 'evt_dlocal_new',
      'status' => 'past_due',
      'created_at' => '2026-03-28T12:00:00Z',
      'data' => ['subscription_id' => 'sub_dlocal_003'],
   ];

   $oldPayload = [
      'id' => 'evt_dlocal_old',
      'status' => 'approved',
      'created_at' => '2026-03-27T12:00:00Z',
      'data' => ['subscription_id' => 'sub_dlocal_003'],
   ];

   $newRawPayload = json_encode($newPayload, JSON_UNESCAPED_SLASHES);
   $oldRawPayload = json_encode($oldPayload, JSON_UNESCAPED_SLASHES);

   $newSignature = hash_hmac('sha256', (string) $newRawPayload, 'secret-test');
   $oldSignature = hash_hmac('sha256', (string) $oldRawPayload, 'secret-test');

   $this->call(
      'POST',
      '/billing/webhooks/dlocal',
      [],
      [],
      [],
      [
         'CONTENT_TYPE' => 'application/json',
         'HTTP_X_DLOCAL_SIGNATURE' => $newSignature,
      ],
      $newRawPayload,
   )->assertOk();

   $this->call(
      'POST',
      '/billing/webhooks/dlocal',
      [],
      [],
      [],
      [
         'CONTENT_TYPE' => 'application/json',
         'HTTP_X_DLOCAL_SIGNATURE' => $oldSignature,
      ],
      $oldRawPayload,
   )->assertOk();

   $subscription->refresh();

   expect($subscription->status)->toBe('past_due');
});

test('billing central exige autenticacion para pantalla principal', function () {
   $this->get(route('central.billing.index'))->assertRedirect();

   $user = User::factory()->unverified()->create();
   $this->actingAs($user, 'central');

   $this->get(route('central.billing.index'))->assertForbidden();
});

test('idempotencia dlocal se mantiene ante entregas repetidas', function () {
   config()->set('services.dlocal.webhook_secret', 'secret-test');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-dlocal-4',
      'data' => ['name' => 'Tenant Dlocal 4'],
   ]));

   $plan = Plan::query()->create([
      'name' => 'Plan Dlocal 4',
      'slug' => 'plan-dlocal-4',
      'price_monthly_cents' => 4000,
      'price_yearly_cents' => 40000,
      'trial_days' => 7,
      'features' => ['api_access'],
      'is_active' => true,
      'sort_order' => 4,
   ]);

   TenantSubscription::query()->create([
      'tenant_id' => $tenant->id,
      'plan_id' => $plan->id,
      'billing_period' => 'monthly',
      'status' => 'active',
      'starts_at' => now()->toDateTimeString(),
      'price_snapshot_cents' => 4000,
      'external_id' => 'sub_dlocal_004',
      'meta' => [],
   ]);

   $payload = [
      'id' => 'evt_dlocal_concurrent',
      'status' => 'approved',
      'created_at' => '2026-03-28T13:00:00Z',
      'data' => ['subscription_id' => 'sub_dlocal_004'],
   ];

   $rawPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
   $signature = hash_hmac('sha256', (string) $rawPayload, 'secret-test');

   foreach (range(1, 5) as $_) {
      $this->call(
         'POST',
         '/billing/webhooks/dlocal',
         [],
         [],
         [],
         [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_DLOCAL_SIGNATURE' => $signature,
         ],
         $rawPayload,
      )->assertOk();
   }

   $processedCount = DB::connection('central')
      ->table('processed_webhooks')
      ->where('provider', 'dlocal')
      ->where('event_id', 'evt_dlocal_concurrent')
      ->count();

   expect($processedCount)->toBe(1);
});
