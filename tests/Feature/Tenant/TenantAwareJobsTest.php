<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Shared\Infrastructure\Jobs\Middleware\EnsureTenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper;

final class WriteTenantScopedFileJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $path,
        private readonly string $contents,
    ) {
        $this->onConnection('database');
        $this->onQueue('default');
    }

    /**
     * @return array<int, EnsureTenantContext>
     */
    public function middleware(): array
    {
        return [new EnsureTenantContext];
    }

    public function handle(): void
    {
        Storage::disk('local')->put($this->path, $this->contents);
    }
}

final class AssertTenantContextJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public static ?string $handledTenantId = null;

    public function __construct()
    {
        $this->onConnection('database');
        $this->onQueue('default');
    }

    /**
     * @return array<int, EnsureTenantContext>
     */
    public function middleware(): array
    {
        return [new EnsureTenantContext];
    }

    public function handle(): void
    {
        self::$handledTenantId = tenant()?->getTenantKey();
    }
}

final class FailWithoutTenantContextJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @return array<int, EnsureTenantContext>
     */
    public function middleware(): array
    {
        return [new EnsureTenantContext];
    }

    public function handle(): void {}
}

function createTenantForQueuedJob(string $id): Tenant
{
    /** @var Tenant $tenant */
    $tenant = Tenant::withoutEvents(fn () => Tenant::query()->create([
        'id' => $id,
        'name' => 'Tenant '.$id,
        'status' => 'active',
        'region' => 'us-east-1',
        'tenancy_db_name' => 'tenant_'.str_replace('-', '_', $id),
    ]));

    Domain::query()->create([
        'tenant_id' => $tenant->id,
        'domain' => "{$id}.localhost",
        'verified_at' => now(),
    ]);

    return $tenant;
}

function workOneTenantAwareJob(): void
{
    Artisan::call('queue:work', [
        'connection' => 'database',
        '--queue' => 'default',
        '--once' => true,
        '--tries' => 1,
    ]);
}

beforeEach(function (): void {
    config()->set('queue.default', 'database');
    config()->set('queue.connections.database.connection', 'central');
    config()->set('tenancy.bootstrappers', [
        FilesystemTenancyBootstrapper::class,
        QueueTenancyBootstrapper::class,
    ]);

    AssertTenantContextJob::$handledTenantId = null;
});

test('job queued desde tenant guarda tenant_id en el payload central', function (): void {
    $tenant = createTenantForQueuedJob('tenant-job-payload');

    tenancy()->initialize($tenant);

    try {
        AssertTenantContextJob::dispatch();
    } finally {
        tenancy()->end();
    }

    $job = DB::connection('central')->table('jobs')->latest('id')->first();

    expect($job)->not->toBeNull();

    /** @var object{payload:string} $job */
    $payload = json_decode($job->payload, true, 512, JSON_THROW_ON_ERROR);

    expect($payload['tenant_id'] ?? null)->toBe($tenant->id);
});

test('worker restaura contexto tenant correcto al procesar job asincrono', function (): void {
    $tenant = createTenantForQueuedJob('tenant-job-restore');

    tenancy()->initialize($tenant);

    try {
        WriteTenantScopedFileJob::dispatch('job-proof.txt', 'processed-in-'.$tenant->id);
        AssertTenantContextJob::dispatch();
    } finally {
        tenancy()->end();
    }

    expect(tenancy()->initialized)->toBeFalse();

    workOneTenantAwareJob();
    workOneTenantAwareJob();

    expect(AssertTenantContextJob::$handledTenantId)->toBe($tenant->id)
        ->and(tenancy()->initialized)->toBeFalse();

    expect(is_file(storage_path('app/tenants/'.$tenant->id.'/job-proof.txt')))->toBeTrue();
    expect(file_get_contents(storage_path('app/tenants/'.$tenant->id.'/job-proof.txt')))->toBe('processed-in-'.$tenant->id);
});

test('jobs de tenants distintos restauran su propio contexto y no fugan datos', function (): void {
    $tenantA = createTenantForQueuedJob('tenant-job-a');
    $tenantB = createTenantForQueuedJob('tenant-job-b');

    tenancy()->initialize($tenantA);

    try {
        WriteTenantScopedFileJob::dispatch('tenant-proof.txt', 'tenant-a');
    } finally {
        tenancy()->end();
    }

    tenancy()->initialize($tenantB);

    try {
        WriteTenantScopedFileJob::dispatch('tenant-proof.txt', 'tenant-b');
    } finally {
        tenancy()->end();
    }

    workOneTenantAwareJob();
    workOneTenantAwareJob();

    $pathA = storage_path('app/tenants/'.$tenantA->id.'/tenant-proof.txt');
    $pathB = storage_path('app/tenants/'.$tenantB->id.'/tenant-proof.txt');

    expect(is_file($pathA))->toBeTrue();
    expect(is_file($pathB))->toBeTrue();
    expect(file_get_contents($pathA))->toBe('tenant-a');
    expect(file_get_contents($pathB))->toBe('tenant-b');
});

test('middleware bloquea ejecucion de job tenant-aware sin contexto tenant', function (): void {
    config()->set('queue.default', 'sync');

    expect(fn () => FailWithoutTenantContextJob::dispatch())
        ->toThrow(RuntimeException::class, 'se ejecuto sin contexto tenant inicializado');
});
