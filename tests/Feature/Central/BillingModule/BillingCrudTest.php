<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\BillingModule\Livewire\BillingCrud;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('usuarios autenticados pueden ver billing central', function () {
   $user = User::factory()->create();
   actingAs($user, 'central');

   get(route('central.billing.index'))
      ->assertOk();
});

test('billing crud crea un plan desde livewire', function () {
   $user = User::factory()->create();
   actingAs($user, 'central');

   Livewire::test(BillingCrud::class)
      ->set('planForm.name', 'Plan Pro')
      ->set('planForm.slug', 'plan-pro')
      ->set('planForm.priceMonthlyCents', 1900)
      ->set('planForm.priceYearlyCents', 19000)
      ->set('planForm.trialDays', 14)
      ->set('planForm.features', 'api_access, priority_support')
      ->set('planForm.isActive', true)
      ->set('planForm.sortOrder', 1)
      ->call('createPlan')
      ->assertHasNoErrors();

   $exists = DB::connection('central')->table('plans')
      ->where('slug', 'plan-pro')
      ->where('price_monthly_cents', 1900)
      ->where('trial_days', 14)
      ->exists();

   expect($exists)->toBeTrue();
});
