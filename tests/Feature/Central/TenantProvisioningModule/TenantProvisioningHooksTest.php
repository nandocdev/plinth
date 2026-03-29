<?php

use App\Central\TenantProvisioningModule\Actions\ListTenantProvisioningHooksAction;
use App\Central\TenantProvisioningModule\Actions\QueueTenantProvisioningHooksAction;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use App\Central\TenantProvisioningModule\Jobs\RunTenantProvisioningHooksJob;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantProvisioningHookRun;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;

test('resuelve hooks de provisioning con placeholders del tenant', function (): void {
   Config::set('tenant_provisioning.hooks', [
      'terraform' => [
         'driver' => 'terraform',
         'enabled' => true,
         'timeout' => 900,
         'working_directory' => base_path('ops/terraform'),
         'command' => [
            'terraform',
            'apply',
            '-var=tenant_id={tenant_id}',
            '-var=tenant_domain={tenant_domain}',
            '-var=tenant_database={tenant_database}',
         ],
         'environment' => [
            'PLINTH_TENANT_ID' => '{tenant_id}',
            'PLINTH_TENANT_CONTEXT' => '{tenant_context_json}',
         ],
      ],
      'ansible' => [
         'driver' => 'ansible',
         'enabled' => true,
         'timeout' => 600,
         'working_directory' => base_path('ops/ansible'),
         'command' => [
            'ansible-playbook',
            'provision-tenant.yml',
            '--extra-vars',
            'tenant_domain={tenant_domain} tenant_region={tenant_region}',
         ],
         'environment' => [
            'PLINTH_TENANT_DOMAIN' => '{tenant_domain}',
         ],
      ],
   ]);

   $tenant = createProvisioningHookTenant('tenant-hooks-resolve');

   $hooks = app(ListTenantProvisioningHooksAction::class)->execute($tenant);

   expect($hooks)->toHaveCount(2)
      ->and($hooks[0]->command)->toContain('terraform')
      ->and($hooks[0]->command)->toContain('-var=tenant_id=' . $tenant->id)
      ->and($hooks[0]->command)->toContain('-var=tenant_domain=' . tenantHookDomain($tenant))
      ->and($hooks[0]->environment['PLINTH_TENANT_ID'])->toBe($tenant->id)
      ->and($hooks[0]->environment['PLINTH_TENANT_CONTEXT'])->toContain($tenant->id)
      ->and($hooks[1]->workingDirectory)->toBe(base_path('ops/ansible'))
      ->and($hooks[1]->command)->toContain('tenant_domain=' . tenantHookDomain($tenant) . ' tenant_region=' . $tenant->region());
});

test('queue action crea runs pendientes y encola job unico', function (): void {
   Bus::fake();
   Config::set('tenant_provisioning.queue', 'infra-hooks');
   Config::set('tenant_provisioning.hooks', provisioningHookConfig());

   $tenant = createProvisioningHookTenant('tenant-hooks-queue');

   $queued = app(QueueTenantProvisioningHooksAction::class)->execute($tenant);

   expect($queued)->toBe(2)
      ->and(TenantProvisioningHookRun::query()->where('tenant_id', $tenant->id)->count())->toBe(2)
      ->and(TenantProvisioningHookRun::query()->where('tenant_id', $tenant->id)->where('status', TenantProvisioningHookRun::STATUS_PENDING)->count())->toBe(2);

   Bus::assertDispatched(RunTenantProvisioningHooksJob::class);
});

test('tenant created event dispara listener para hooks de provisioning', function (): void {
   Bus::fake();
   Config::set('tenant_provisioning.hooks', [
      'terraform' => provisioningHookConfig()['terraform'],
   ]);

   $tenant = createProvisioningHookTenant('tenant-hooks-event');

   event(new TenantCreatedFromCentral($tenant));

   expect(TenantProvisioningHookRun::query()->where('tenant_id', $tenant->id)->count())->toBe(1);

   Bus::assertDispatched(RunTenantProvisioningHooksJob::class);
});

test('job ejecuta hooks terraform y ansible y marca runs completados', function (): void {
   Process::fake(fn() => Process::result('ok', '', 0));
   Config::set('tenant_provisioning.hooks', provisioningHookConfig());

   $tenant = createProvisioningHookTenant('tenant-hooks-success');
   seedPendingProvisioningHookRuns($tenant);

   RunTenantProvisioningHooksJob::dispatchSync($tenant->id);

   $runs = TenantProvisioningHookRun::query()
      ->where('tenant_id', $tenant->id)
      ->orderBy('hook_name')
      ->get();

   expect($runs)->toHaveCount(2)
      ->and($runs[0]->status)->toBe(TenantProvisioningHookRun::STATUS_COMPLETED)
      ->and($runs[0]->exit_code)->toBe(0)
      ->and($runs[1]->status)->toBe(TenantProvisioningHookRun::STATUS_COMPLETED)
      ->and($runs[1]->exit_code)->toBe(0);

   Process::assertRanTimes(fn($process) => is_array($process->command), 2);
   Process::assertRan(fn($process) => $process->command === [
      'terraform',
      'apply',
      '-auto-approve',
      '-var=tenant_id=' . $tenant->id,
      '-var=tenant_domain=' . tenantHookDomain($tenant),
   ]);
});

test('job marca run como failed cuando comando externo falla', function (): void {
   Process::fake(fn() => Process::result('', 'terraform exploded', 1));
   Config::set('tenant_provisioning.hooks', [
      'terraform' => provisioningHookConfig()['terraform'],
   ]);

   $tenant = createProvisioningHookTenant('tenant-hooks-failed');
   seedPendingProvisioningHookRuns($tenant);

   expect(fn() => RunTenantProvisioningHooksJob::dispatchSync($tenant->id))
      ->toThrow(RuntimeException::class);

   $run = TenantProvisioningHookRun::query()
      ->where('tenant_id', $tenant->id)
      ->where('hook_name', 'terraform')
      ->first();

   expect($run)->not->toBeNull()
      ->and($run?->status)->toBe(TenantProvisioningHookRun::STATUS_FAILED)
      ->and($run?->exit_code)->toBe(1)
      ->and($run?->error_output)->toContain('terraform exploded');
});

function createProvisioningHookTenant(string $tenantId): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $tenantId,
      'name' => 'Tenant ' . $tenantId,
      'status' => 'active',
      'region' => 'eu-west-1',
      'tenancy_db_connection' => 'tenant_template_eu_west_1',
      'tenancy_db_name' => 'tenant_eu_west_1_' . str_replace('-', '_', $tenantId),
   ]));

   Domain::query()->create([
      'domain' => $tenantId . '.app.test',
      'tenant_id' => $tenant->id,
   ]);

   return $tenant;
}

/**
 * @return array<string, array<string, mixed>>
 */
function provisioningHookConfig(): array {
   return [
      'terraform' => [
         'driver' => 'terraform',
         'enabled' => true,
         'timeout' => 900,
         'working_directory' => base_path('ops/terraform'),
         'command' => [
            'terraform',
            'apply',
            '-auto-approve',
            '-var=tenant_id={tenant_id}',
            '-var=tenant_domain={tenant_domain}',
         ],
         'environment' => [
            'PLINTH_TENANT_ID' => '{tenant_id}',
         ],
      ],
      'ansible' => [
         'driver' => 'ansible',
         'enabled' => true,
         'timeout' => 600,
         'working_directory' => base_path('ops/ansible'),
         'command' => [
            'ansible-playbook',
            'provision-tenant.yml',
            '--extra-vars',
            'tenant_id={tenant_id} tenant_domain={tenant_domain}',
         ],
         'environment' => [
            'PLINTH_TENANT_DOMAIN' => '{tenant_domain}',
         ],
      ],
   ];
}

function seedPendingProvisioningHookRuns(Tenant $tenant): void {
   $hooks = app(ListTenantProvisioningHooksAction::class)->execute($tenant);

   foreach ($hooks as $hook) {
      TenantProvisioningHookRun::query()->create([
         'tenant_id' => $tenant->id,
         'hook_name' => $hook->name,
         'driver' => $hook->driver,
         'status' => TenantProvisioningHookRun::STATUS_PENDING,
         'command' => $hook->command,
         'working_directory' => $hook->workingDirectory,
         'environment' => $hook->environment,
         'meta' => [
            'timeout' => $hook->timeout,
         ],
      ]);
   }
}

function tenantHookDomain(Tenant $tenant): string {
   return (string) $tenant->domains()->value('domain');
}
