<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\SystemHealthModule\Livewire\SystemHealthDashboard;
use Livewire\Livewire;

test('system health dashboard requiere autenticacion central', function () {
   $this->get(route('central.health.index'))
      ->assertRedirect(route('login'));
});

test('system health dashboard renderiza metricas y filtros', function () {
   $user = User::factory()->withTwoFactor()->create();
   $this->actingAs($user, 'central');

   $this->get(route('central.health.index'))
      ->assertOk()
      ->assertSee('System health')
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
