<?php

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

   $tenant = Tenant::query()->create([
      'id' => 'tenant-dlocal-1',
      'data' => ['name' => 'Tenant Dlocal'],
   ]);

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

   $tenant = Tenant::query()->create([
      'id' => 'tenant-dlocal-2',
      'data' => ['name' => 'Tenant Dlocal 2'],
   ]);

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
