<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\SystemHealthModule\Livewire\SystemHealthDashboard;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('system health dashboard requiere autenticacion central', function () {
   $this->get(route('central.health.index'))
      ->assertRedirect(route('login'));
});

test('system health dashboard renderiza metricas y filtros', function () {
   $user = User::factory()->withTwoFactor()->create();
   $this->actingAs($user, 'central');

   DB::connection('central')->table('tenants')->insert([
      [
         'id' => 'acme-metrics',
         'data' => json_encode(['status' => 'active'], JSON_THROW_ON_ERROR),
         'created_at' => now(),
         'updated_at' => now(),
      ],
      [
         'id' => 'globex-metrics',
         'data' => json_encode(['status' => 'suspended'], JSON_THROW_ON_ERROR),
         'created_at' => now(),
         'updated_at' => now(),
      ],
   ]);

   $planId = DB::connection('central')->table('plans')->insertGetId([
      'name' => 'Growth Metrics',
      'slug' => 'growth-metrics',
      'price_monthly_cents' => 10000,
      'price_yearly_cents' => 100000,
      'trial_days' => 14,
      'features' => json_encode([], JSON_THROW_ON_ERROR),
      'is_active' => true,
      'sort_order' => 1,
      'max_users_soft' => null,
      'max_users_hard' => null,
      'max_storage_mb_soft' => null,
      'max_storage_mb_hard' => null,
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   $subscriptionAcmeId = DB::connection('central')->table('tenant_subscriptions')->insertGetId([
      'tenant_id' => 'acme-metrics',
      'plan_id' => $planId,
      'billing_period' => 'monthly',
      'status' => 'active',
      'trial_ends_at' => null,
      'starts_at' => now()->subDays(10),
      'ends_at' => null,
      'price_snapshot_cents' => 10000,
      'external_id' => 'sub_acme_metrics',
      'meta' => json_encode([], JSON_THROW_ON_ERROR),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   $subscriptionGlobexId = DB::connection('central')->table('tenant_subscriptions')->insertGetId([
      'tenant_id' => 'globex-metrics',
      'plan_id' => $planId,
      'billing_period' => 'yearly',
      'status' => 'trialing',
      'trial_ends_at' => now()->addDays(7),
      'starts_at' => now()->subDays(5),
      'ends_at' => null,
      'price_snapshot_cents' => 120000,
      'external_id' => 'sub_globex_metrics',
      'meta' => json_encode([], JSON_THROW_ON_ERROR),
      'created_at' => now(),
      'updated_at' => now(),
   ]);

   DB::connection('central')->table('tenant_invoices')->insert([
      [
         'tenant_id' => 'acme-metrics',
         'subscription_id' => $subscriptionAcmeId,
         'invoice_number' => 'INV-METRICS-001',
         'currency' => 'USD',
         'amount_cents' => 10000,
         'status' => 'paid',
         'billing_period' => 'monthly',
         'description' => 'Invoice 1',
         'paid_at' => now()->subDay(),
         'meta' => json_encode([], JSON_THROW_ON_ERROR),
         'created_at' => now(),
         'updated_at' => now(),
      ],
      [
         'tenant_id' => 'globex-metrics',
         'subscription_id' => $subscriptionGlobexId,
         'invoice_number' => 'INV-METRICS-002',
         'currency' => 'USD',
         'amount_cents' => 20000,
         'status' => 'paid',
         'billing_period' => 'yearly',
         'description' => 'Invoice 2',
         'paid_at' => now()->subDay(),
         'meta' => json_encode([], JSON_THROW_ON_ERROR),
         'created_at' => now(),
         'updated_at' => now(),
      ],
   ]);

   $this->get(route('central.health.index'))
      ->assertOk()
      ->assertSee('System health')
      ->assertSee('Revenue mensual (USD)')
      ->assertSee('$300.00')
      ->assertSee('MRR estimado (USD)')
      ->assertSee('Database connections')
      ->assertSee('Queue status')
      ->assertSee('Storage usage');

   Livewire::test(SystemHealthDashboard::class)
      ->set('filterForm.connectionFilter', 'central')
      ->set('filterForm.onlyUnhealthy', false)
      ->assertSee('central')
      ->call('clearFilters')
      ->assertSet('filterForm.connectionFilter', 'all')
      ->assertSet('filterForm.onlyUnhealthy', false);
});
