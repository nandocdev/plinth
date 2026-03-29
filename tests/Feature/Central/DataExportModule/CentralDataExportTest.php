<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\DataExportModule\Actions\BuildCentralDataExportPayloadAction;
use App\Central\DataExportModule\Actions\QueueCentralDataExportAction;
use App\Central\DataExportModule\DTOs\RequestCentralDataExportData;
use App\Central\DataExportModule\Jobs\GenerateCentralDataExportJob;
use App\Central\DataExportModule\Livewire\CentralDataExportManager;
use App\Central\DataExportModule\Models\CentralDataExport;
use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Livewire\Livewire;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

test('pantalla de exportaciones GDPR requiere autenticacion central', function (): void {
   $this->get(route('central.exports.index'))
      ->assertRedirect(route('login'));
});

test('livewire puede encolar exportacion GDPR desde central', function (): void {
   Bus::fake();

   $user = User::factory()->withTwoFactor()->create();
   $tenant = createCentralExportTenant('tenant-export-livewire');

   $this->actingAs($user, 'central');

   Livewire::test(CentralDataExportManager::class)
      ->set('form.tenantId', $tenant->id)
      ->set('form.includeActivityLog', true)
      ->call('requestExport')
      ->assertHasNoErrors();

   $export = CentralDataExport::query()->where('tenant_id', $tenant->id)->first();

   expect($export)->not->toBeNull()
      ->and($export?->status)->toBe(CentralDataExport::STATUS_PENDING)
      ->and($export?->include_activity_log)->toBeTrue();

   Bus::assertDispatched(GenerateCentralDataExportJob::class);
});

test('queue action evita exportaciones duplicadas activas por tenant', function (): void {
   Queue::fake();

   $user = User::factory()->withTwoFactor()->create();
   $tenant = createCentralExportTenant('tenant-export-duplicate');

   $action = app(QueueCentralDataExportAction::class);

   $action->execute(new RequestCentralDataExportData($tenant->id, $user->id, true));

   expect(fn () => $action->execute(new RequestCentralDataExportData($tenant->id, $user->id, false)))
      ->toThrow(RuntimeException::class);
});

test('build payload incluye datos centrales del tenant para cumplimiento GDPR', function (): void {
   $user = User::factory()->withTwoFactor()->create();
   $tenant = createCentralExportTenant('tenant-export-payload');

   activity('central_audit')
      ->causedBy($user)
      ->withProperties(['tenant_id' => $tenant->id, 'route_name' => 'central.exports.index'])
      ->event('post.request')
      ->log('Requested GDPR export');

   $export = CentralDataExport::query()->create([
      'tenant_id' => $tenant->id,
      'requested_by_user_id' => $user->id,
      'export_uuid' => (string) \Illuminate\Support\Str::uuid(),
      'format' => 'zip',
      'status' => CentralDataExport::STATUS_PENDING,
      'include_activity_log' => true,
      'meta' => [],
   ]);

   $payload = app(BuildCentralDataExportPayloadAction::class)->execute($export);

   expect($payload->tenant['id'])->toBe($tenant->id)
      ->and($payload->domains)->toHaveCount(1)
      ->and($payload->billing['subscription'])->toBeNull()
      ->and($payload->activityLog)->toHaveCount(1)
      ->and($payload->manifest()['counts']['domains'])->toBe(1);
});

test('job genera zip GDPR y marca exportacion como completed', function (): void {
   $user = User::factory()->withTwoFactor()->create();
   $tenant = createCentralExportTenant('tenant-export-job');

   $export = CentralDataExport::query()->create([
      'tenant_id' => $tenant->id,
      'requested_by_user_id' => $user->id,
      'export_uuid' => (string) \Illuminate\Support\Str::uuid(),
      'format' => 'zip',
      'status' => CentralDataExport::STATUS_PENDING,
      'include_activity_log' => true,
      'meta' => [],
   ]);

   GenerateCentralDataExportJob::dispatchSync($export->id);

   $export->refresh();

   expect($export->status)->toBe(CentralDataExport::STATUS_COMPLETED)
      ->and($export->file_path)->not->toBeNull()
      ->and(is_file((string) $export->file_path))->toBeTrue()
      ->and($export->size_bytes)->toBeGreaterThan(0);
});

test('controller permite descargar exportacion completada', function (): void {
   $user = User::factory()->withTwoFactor()->create();
   $tenant = createCentralExportTenant('tenant-export-download');

   $directory = storage_path('app/private/central-data-exports/' . $tenant->id . '/download');
   if (! is_dir($directory)) {
      mkdir($directory, 0755, true);
   }
   $archivePath = $directory . '/tenant-' . $tenant->id . '-gdpr-export.zip';
   file_put_contents($archivePath, 'zip');

   $export = CentralDataExport::query()->create([
      'tenant_id' => $tenant->id,
      'requested_by_user_id' => $user->id,
      'export_uuid' => (string) \Illuminate\Support\Str::uuid(),
      'format' => 'zip',
      'status' => CentralDataExport::STATUS_COMPLETED,
      'include_activity_log' => true,
      'file_path' => $archivePath,
      'size_bytes' => 3,
      'completed_at' => now(),
      'meta' => [],
   ]);

   $this->actingAs($user, 'central')
      ->get(route('central.exports.download', $export))
      ->assertOk();
});

test('job marca exportacion failed si no puede preparar el directorio del zip', function (): void {
   $user = User::factory()->withTwoFactor()->create();
   $tenant = createCentralExportTenant('tenant-export-failed');

   $export = CentralDataExport::query()->create([
      'tenant_id' => $tenant->id,
      'requested_by_user_id' => $user->id,
      'export_uuid' => (string) \Illuminate\Support\Str::uuid(),
      'format' => 'zip',
      'status' => CentralDataExport::STATUS_PENDING,
      'include_activity_log' => true,
      'meta' => [],
   ]);

   $directoryPath = storage_path('app/private/central-data-exports/' . $tenant->id . '/' . $export->id);
   $parentDirectory = dirname($directoryPath);
   if (! is_dir($parentDirectory)) {
      mkdir($parentDirectory, 0755, true);
   }
   touch($directoryPath);

   expect(fn () => GenerateCentralDataExportJob::dispatchSync($export->id))
      ->toThrow(ErrorException::class);

   $export->refresh();

   expect($export->status)->toBe(CentralDataExport::STATUS_FAILED)
   ->and($export->error_message)->not->toBeNull();
});

function createCentralExportTenant(string $tenantId): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn () => Tenant::query()->create([
      'id' => $tenantId,
      'name' => 'Tenant ' . $tenantId,
      'status' => 'active',
      'region' => 'us-east-1',
      'branding' => ['brand_name' => 'Tenant ' . $tenantId],
   ]));

   Domain::query()->create([
      'domain' => $tenantId . '.app.test',
      'tenant_id' => $tenant->id,
   ]);

   return $tenant;
}