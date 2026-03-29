<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\ActivityLogModule\Livewire\GlobalLogsViewer;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Livewire\Livewire;

test('logs viewer central requiere autenticacion', function () {
   $this->get(route('central.logs.index'))
      ->assertRedirect(route('login'));
});

test('logs viewer central lista logs y filtra por tenant', function () {
   $user = User::factory()->create();
   $this->actingAs($user, 'central');

   /** @var Tenant $tenantA */
   $tenantA = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-log-a',
      'data' => ['name' => 'Tenant Log A', 'status' => 'active'],
   ]));

   /** @var Tenant $tenantB */
   $tenantB = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-log-b',
      'data' => ['name' => 'Tenant Log B', 'status' => 'active'],
   ]));

   $logPath = storage_path('logs/testing-global-viewer.log');

   config()->set('logging.channels.single.path', $logPath);

   file_put_contents($logPath, implode(PHP_EOL, [
      '[2026-03-29 12:00:00] local.INFO: Tenant A operation success {"tenant_id":"tenant-log-a","event":"provisioned"}',
      '[2026-03-29 12:01:00] local.ERROR: Tenant B webhook failed {"tenant_id":"tenant-log-b","event":"billing"}',
      '[2026-03-29 12:02:00] local.WARNING: Central warning without tenant',
   ]) . PHP_EOL);

   try {
      $this->get(route('central.logs.index'))
         ->assertOk()
         ->assertSee('Global logs');

      Livewire::test(GlobalLogsViewer::class)
         ->assertSee('Tenant A operation success')
         ->assertSee('Tenant B webhook failed')
         ->set('filterForm.tenantId', $tenantA->id)
         ->assertSee('Tenant A operation success')
         ->assertDontSee('Tenant B webhook failed')
         ->set('filterForm.level', 'error')
         ->assertDontSee('Tenant A operation success');
   } finally {
      if (is_file($logPath)) {
         unlink($logPath);
      }
   }

   expect($tenantB->id)->toBe('tenant-log-b');
});
