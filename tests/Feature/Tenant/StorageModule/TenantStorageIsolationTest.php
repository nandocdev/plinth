<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\Storage;

/**
 * Verifica que FilesystemTenancyBootstrapper aísla correctamente el disco
 * 'local' y 'public' por tenant usando la ruta storage/app/tenants/{uuid}/.
 */

function createStorageTenant(string $id): Tenant
{
    /** @var Tenant $tenant */
    $tenant = Tenant::withoutEvents(fn () => Tenant::query()->create([
        'id'              => $id,
        'name'            => 'Storage Tenant ' . $id,
        'status'          => 'active',
        'region'          => 'us-east-1',
        'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
    ]));

    Domain::query()->create([
        'tenant_id'   => $tenant->id,
        'domain'      => "{$id}.localhost",
        'verified_at' => now(),
    ]);

    return $tenant;
}

it('el disco local apunta a storage/app/tenants/{uuid}/ en contexto tenant', function (): void {
    $tenant = createStorageTenant('storage-path-test');

    tenancy()->initialize($tenant);

    try {
        $expectedRoot = storage_path('app/tenants/' . $tenant->id . '/');
        $actualRoot   = Storage::disk('local')->path('');

        expect(rtrim($actualRoot, '/'))->toBe(rtrim($expectedRoot, '/'));
    } finally {
        tenancy()->end();
    }
});

it('el disco public apunta a storage/app/tenants/{uuid}/public/ en contexto tenant', function (): void {
    $tenant = createStorageTenant('storage-public-path-test');

    tenancy()->initialize($tenant);

    try {
        $expectedRoot = storage_path('app/tenants/' . $tenant->id . '/public/');
        $actualRoot   = Storage::disk('public')->path('');

        expect(rtrim($actualRoot, '/'))->toBe(rtrim($expectedRoot, '/'));
    } finally {
        tenancy()->end();
    }
});

it('archivo escrito en tenant A no es visible en tenant B', function (): void {
    $tenantA = createStorageTenant('storage-iso-a');
    $tenantB = createStorageTenant('storage-iso-b');

    // Escribir en tenant A
    tenancy()->initialize($tenantA);
    Storage::disk('local')->put('secret.txt', 'datos-del-tenant-a');
    tenancy()->end();

    // Tenant B no debe ver ese archivo
    tenancy()->initialize($tenantB);
    try {
        expect(Storage::disk('local')->exists('secret.txt'))->toBeFalse();
    } finally {
        Storage::disk('local')->deleteDirectory(''); // limpiar tenant B
        tenancy()->end();
    }

    // Limpiar tenant A
    tenancy()->initialize($tenantA);
    Storage::disk('local')->deleteDirectory('');
    tenancy()->end();
});

it('archivo escrito en tenant A es visible solo para tenant A', function (): void {
    $tenant = createStorageTenant('storage-write-read');

    tenancy()->initialize($tenant);

    try {
        Storage::disk('local')->put('invoice.pdf', 'contenido-de-factura');

        expect(Storage::disk('local')->exists('invoice.pdf'))->toBeTrue();
        expect(Storage::disk('local')->get('invoice.pdf'))->toBe('contenido-de-factura');
    } finally {
        Storage::disk('local')->deleteDirectory('');
        tenancy()->end();
    }
});

it('despues de tenancy end el disco local vuelve al root original', function (): void {
    $tenant = createStorageTenant('storage-revert-test');

    $originalRoot = Storage::disk('local')->path('');

    tenancy()->initialize($tenant);
    $tenantRoot = Storage::disk('local')->path('');
    tenancy()->end();

    $restoredRoot = Storage::disk('local')->path('');

    expect($tenantRoot)->not->toBe($originalRoot);
    expect(rtrim($restoredRoot, '/'))->toBe(rtrim($originalRoot, '/'));
});

it('aislamiento storage no filtra entre multiples tenants en secuencia', function (): void {
    $tenants = collect(['storage-seq-1', 'storage-seq-2', 'storage-seq-3'])
        ->map(fn (string $id) => createStorageTenant($id));

    // Cada tenant escribe su propio archivo
    $tenants->each(function (Tenant $tenant): void {
        tenancy()->initialize($tenant);
        Storage::disk('local')->put('data.txt', "tenant-{$tenant->id}");
        tenancy()->end();
    });

    // Verificar que cada tenant solo ve sus propios datos
    $tenants->each(function (Tenant $tenant): void {
        tenancy()->initialize($tenant);
        try {
            $content = Storage::disk('local')->get('data.txt');
            expect($content)->toBe("tenant-{$tenant->id}");
        } finally {
            Storage::disk('local')->deleteDirectory('');
            tenancy()->end();
        }
    });
});
