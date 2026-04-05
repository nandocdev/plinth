<?php

declare(strict_types=1);

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\ReportingModule\Actions\GetAnalyticsSummaryAction;
use App\Tenant\ReportingModule\Actions\GetMetricSeriesAction;
use App\Tenant\ReportingModule\Actions\TakeMetricSnapshotAction;
use App\Tenant\ReportingModule\DTOs\AnalyticsPeriodData;
use App\Tenant\ReportingModule\Enums\TenantMetricKey;
use App\Tenant\ReportingModule\Jobs\CollectDailyMetricsJob;
use App\Tenant\ReportingModule\Models\TenantMetricSnapshot;
use App\Tenant\ReportingModule\Policies\ReportingPolicy;
use App\Tenant\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

// ── TakeMetricSnapshotAction ──────────────────────────────────────────────────

test('TakeMetricSnapshotAction persiste todas las métricas para la fecha de hoy', function (): void {
    // Crea algunos usuarios para que el conteo no sea cero
    User::factory()->count(3)->create();

    app(TakeMetricSnapshotAction::class)->execute();

    expect(TenantMetricSnapshot::query()->count())
        ->toBeGreaterThanOrEqual(count(TenantMetricKey::cases()));

    $snap = TenantMetricSnapshot::query()
        ->where('metric_key', TenantMetricKey::UsersTotal->value)
        ->first();

    expect($snap)->not->toBeNull()
        ->and((float) $snap?->value)->toBe(3.0);
});

test('TakeMetricSnapshotAction es idempotente al ejecutarse dos veces en el mismo día', function (): void {
    User::factory()->count(2)->create();

    app(TakeMetricSnapshotAction::class)->execute();
    app(TakeMetricSnapshotAction::class)->execute();

    // No debe duplicar filas; la restricción UNIQUE hace upsert
    $count = TenantMetricSnapshot::query()
        ->where('metric_key', TenantMetricKey::UsersTotal->value)
        ->count();

    expect($count)->toBe(1);
});

// ── GetAnalyticsSummaryAction ─────────────────────────────────────────────────

test('GetAnalyticsSummaryAction devuelve ceros si no hay snapshots', function (): void {
    $summary = app(GetAnalyticsSummaryAction::class)->execute();

    expect($summary->usersTotal)->toBe(0)
        ->and($summary->lastSnapshotAt)->toBeNull();
});

test('GetAnalyticsSummaryAction devuelve valores del último snapshot disponible', function (): void {
    TenantMetricSnapshot::query()->insert([
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::UsersTotal->value, 'value' => 5, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::LoginsDay->value, 'value' => 12, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::WebhookDeliveriesSuccess->value, 'value' => 3, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::WebhookDeliveriesFailed->value, 'value' => 1, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::FilesStorageMb->value, 'value' => 42.5, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::ActivityLogEntries->value, 'value' => 200, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::UsersActiveMonth->value, 'value' => 4, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::ApiRequestsDay->value, 'value' => 9, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::FilesUploaded->value, 'value' => 7, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::UsersActiveDay->value, 'value' => 2, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $summary = app(GetAnalyticsSummaryAction::class)->execute();

    expect($summary->usersTotal)->toBe(5)
        ->and($summary->loginsDay)->toBe(12)
        ->and($summary->webhookDeliveriesSuccess)->toBe(3)
        ->and($summary->webhookDeliveriesFailed)->toBe(1)
        ->and($summary->filesStorageMb)->toBe(42.5)
        ->and($summary->activityLogEntries)->toBe(200)
        ->and($summary->lastSnapshotAt)->toBe('2025-01-01');
});

// ── GetMetricSeriesAction ─────────────────────────────────────────────────────

test('GetMetricSeriesAction devuelve puntos ordenados por fecha para el periodo dado', function (): void {
    $rows = [
        ['snapshot_date' => '2025-01-01', 'metric_key' => TenantMetricKey::UsersTotal->value, 'value' => 10, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-02', 'metric_key' => TenantMetricKey::UsersTotal->value, 'value' => 12, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
        ['snapshot_date' => '2025-01-03', 'metric_key' => TenantMetricKey::UsersTotal->value, 'value' => 11, 'meta' => '{}', 'created_at' => now(), 'updated_at' => now()],
    ];
    TenantMetricSnapshot::query()->insert($rows);

    $period = new AnalyticsPeriodData(from: '2025-01-01', to: '2025-01-03', groupBy: 'day');
    $series = app(GetMetricSeriesAction::class)->execute($period, TenantMetricKey::UsersTotal);

    expect($series)->toHaveCount(1);

    $s = $series[0];
    expect($s->key)->toBe(TenantMetricKey::UsersTotal)
        ->and($s->points)->toHaveCount(3)
        ->and($s->total)->toBe(33.0)
        ->and($s->average)->toBe(11.0)
        ->and($s->peak)->toBe(12.0);
});

test('GetMetricSeriesAction devuelve serie vacía si no hay datos en el periodo', function (): void {
    $period = new AnalyticsPeriodData(from: '2024-01-01', to: '2024-01-31', groupBy: 'day');
    $series = app(GetMetricSeriesAction::class)->execute($period, TenantMetricKey::LoginsDay);

    expect($series)->toHaveCount(1)
        ->and($series[0]->points)->toBeEmpty()
        ->and($series[0]->total)->toBe(0.0);
});

// ── CollectDailyMetricsJob ────────────────────────────────────────────────────

test('CollectDailyMetricsJob puede despacharse y tiene middleware EnsureTenantContext', function (): void {
    Bus::fake();

    CollectDailyMetricsJob::dispatch();

    Bus::assertDispatched(CollectDailyMetricsJob::class);

    $job = new CollectDailyMetricsJob;
    expect($job->middleware())->not->toBeEmpty();
});

// ── ReportingPolicy ───────────────────────────────────────────────────────────

test('cualquier usuario autenticado puede ver analytics', function (): void {
    app(SeedDefaultRolesAction::class)->execute();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $member = User::factory()->create();
    $member->assignRole('member');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $policy = new ReportingPolicy;

    expect($policy->viewAny($member))->toBeTrue()
        ->and($policy->viewAny($admin))->toBeTrue();
});

test('solo admin puede recolectar métricas manualmente', function (): void {
    app(SeedDefaultRolesAction::class)->execute();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $member = User::factory()->create();
    $member->assignRole('member');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $policy = new ReportingPolicy;

    expect($policy->collectMetrics($admin))->toBeTrue()
        ->and($policy->collectMetrics($member))->toBeFalse();
});

// ── Gate integration via policy ───────────────────────────────────────────────

test('Gate delega a ReportingPolicy para TenantMetricSnapshot', function (): void {
    app(SeedDefaultRolesAction::class)->execute();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $member = User::factory()->create();
    $member->assignRole('member');

    expect(Gate::forUser($admin)->allows('viewAny', TenantMetricSnapshot::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('collectMetrics', TenantMetricSnapshot::class))->toBeTrue()
        ->and(Gate::forUser($member)->allows('viewAny', TenantMetricSnapshot::class))->toBeTrue()
        ->and(Gate::forUser($member)->allows('collectMetrics', TenantMetricSnapshot::class))->toBeFalse();
});

// ── HTTP: page renders for authenticated tenant user ──────────────────────────

test('usuario tenant autenticado puede acceder a /analytics', function (): void {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();

    $this->actingAs($user, 'tenant')
        ->get('/analytics')
        ->assertOk();
});

test('guest es redirigido al login al intentar acceder a /analytics', function (): void {
    /** @var \Tests\TestCase $this */
    $this->get('/analytics')
        ->assertRedirect();
});
