<?php

use App\Central\AuthenticationModule\Models\User;
use App\Central\TenantProvisioningModule\Actions\QueueTenantRestoreAction;
use App\Central\TenantProvisioningModule\DTOs\QueueTenantRestoreData;
use App\Central\TenantProvisioningModule\Jobs\RunTenantBackupJob;
use App\Central\TenantProvisioningModule\Jobs\RunTenantRestoreJob;
use App\Central\TenantProvisioningModule\Livewire\TenantCrud;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Central\TenantProvisioningModule\Models\TenantRecoverySnapshot;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

test('admin central puede encolar backup por tenant desde livewire', function () {
   Bus::fake();

   $user = User::factory()->create();
   $this->actingAs($user, 'central');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-backup-livewire',
      'data' => ['name' => 'Tenant Backup Livewire', 'status' => 'active'],
   ]));

   Livewire::test(TenantCrud::class)
      ->call('queueBackup', $tenant->id)
      ->assertHasNoErrors();

   $snapshot = TenantRecoverySnapshot::query()
      ->where('tenant_id', $tenant->id)
      ->where('operation', TenantRecoverySnapshot::OPERATION_BACKUP)
      ->latest('id')
      ->first();

   expect($snapshot)->not->toBeNull()
      ->and($snapshot?->status)->toBe(TenantRecoverySnapshot::STATUS_PENDING)
      ->and($snapshot?->requested_by_user_id)->toBe($user->id);

   Bus::assertDispatched(RunTenantBackupJob::class);
});

test('admin central puede encolar restore desde snapshot completado', function () {
   Bus::fake();

   $user = User::factory()->create();
   $this->actingAs($user, 'central');

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-restore-livewire',
      'data' => ['name' => 'Tenant Restore Livewire', 'status' => 'active'],
   ]));

   $backupSnapshot = TenantRecoverySnapshot::query()->create([
      'tenant_id' => $tenant->id,
      'operation' => TenantRecoverySnapshot::OPERATION_BACKUP,
      'status' => TenantRecoverySnapshot::STATUS_COMPLETED,
      'requested_by_user_id' => $user->id,
      'database_dump_path' => '/tmp/fake-dump.sql',
      'storage_archive_path' => '/tmp/fake-storage.zip',
      'manifest_path' => '/tmp/fake-manifest.json',
      'completed_at' => now(),
      'meta' => [],
   ]);

   Livewire::test(TenantCrud::class)
      ->call('restoreTenant', $tenant->id, $backupSnapshot->id)
      ->assertHasNoErrors();

   $restoreSnapshot = TenantRecoverySnapshot::query()
      ->where('tenant_id', $tenant->id)
      ->where('operation', TenantRecoverySnapshot::OPERATION_RESTORE)
      ->latest('id')
      ->first();

   expect($restoreSnapshot)->not->toBeNull()
      ->and($restoreSnapshot?->status)->toBe(TenantRecoverySnapshot::STATUS_PENDING)
      ->and($restoreSnapshot?->source_snapshot_id)->toBe($backupSnapshot->id)
      ->and($restoreSnapshot?->requested_by_user_id)->toBe($user->id);

   Bus::assertDispatched(RunTenantRestoreJob::class);
});

test('restore action rechaza snapshot fuente no completado', function () {
   $user = User::factory()->create();

   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => 'tenant-restore-invalid',
      'data' => ['name' => 'Tenant Restore Invalid', 'status' => 'active'],
   ]));

   $pendingBackupSnapshot = TenantRecoverySnapshot::query()->create([
      'tenant_id' => $tenant->id,
      'operation' => TenantRecoverySnapshot::OPERATION_BACKUP,
      'status' => TenantRecoverySnapshot::STATUS_PENDING,
      'requested_by_user_id' => $user->id,
      'meta' => [],
   ]);

   expect(fn() => app(QueueTenantRestoreAction::class)->execute(
      QueueTenantRestoreData::fromValues($tenant->id, $pendingBackupSnapshot->id, $user->id)
   ))->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
