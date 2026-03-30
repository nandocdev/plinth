<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\CacheManager as TenantCacheManager;

/**
 * Tests de integracion del aislamiento de cache por tenant.
 *
 * El CacheTenancyBootstrapper sustituye el CacheManager de Laravel por
 * TenantCacheManager, el cual aplica automaticamente el tag "tenant{id}"
 * a todas las operaciones de cache. Esto garantiza que dos tenants no
 * lean ni sobreescriban el cache del otro aunque usen la misma clave.
 *
 * Nota: los tests usan el mecanismo de tagging directamente (sin cruzar
 * multiples ciclos initialize/end) porque el ArrayStore no es persistente
 * entre instancias de CacheManager. En produccion con Redis el
 * comportamiento es identico pero los datos persisten entre ciclos.
 */

function createCacheTenant(string $id): Tenant
{
    /** @var Tenant $tenant */
    return Tenant::withoutEvents(fn () => Tenant::query()->create([
        'id'              => $id,
        'name'            => 'Cache Tenant ' . $id,
        'status'          => 'active',
        'region'          => 'us-east-1',
        'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
    ]));
}

beforeEach(function (): void {
    // Solo activar el CacheTenancyBootstrapper para aislar scope del test.
    // El DatabaseTenancyBootstrapper no es necesario para estos tests.
    config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
    Cache::flush();
});

    it('CacheTenancyBootstrapper esta registrado en los bootstrappers de tenancy', function (): void {
        $bootstrappers = config('tenancy.bootstrappers');

        expect($bootstrappers)->toContain(CacheTenancyBootstrapper::class);
    });

    it('tag_base de cache esta configurado y no esta vacio', function (): void {
        expect(config('tenancy.cache.tag_base'))->toBeString()->not->toBeEmpty();
    });

    it('TenantCacheManager se inyecta durante tenancy y se revierte al terminar', function (): void {
        $tenant = createCacheTenant('cache-manager-check');

        expect(app('cache'))->not->toBeInstanceOf(TenantCacheManager::class);

        tenancy()->initialize($tenant);

        try {
            expect(app('cache'))->toBeInstanceOf(TenantCacheManager::class);
        } finally {
            tenancy()->end();
        }

        expect(app('cache'))->not->toBeInstanceOf(TenantCacheManager::class);
    });

    it('operaciones cache dentro del mismo ciclo tenancy put/get/forget funcionan', function (): void {
        $tenant = createCacheTenant('cache-ops-ok');

        tenancy()->initialize($tenant);

        try {
            Cache::put('user_count', 42, 60);
            expect(Cache::get('user_count'))->toBe(42);

            Cache::put('config', ['theme' => 'dark'], 60);
            expect(Cache::get('config'))->toBe(['theme' => 'dark']);

            Cache::forget('user_count');
            expect(Cache::get('user_count'))->toBeNull();
        } finally {
            tenancy()->end();
        }
    });

    it('Cache::remember dentro de tenancy ejecuta y cachea el callback', function (): void {
        $tenant   = createCacheTenant('cache-remember-ok');
        $executed = 0;

        tenancy()->initialize($tenant);

        try {
            $result1 = Cache::remember('heavy_query', 60, function () use (&$executed): string {
                $executed++;

                return 'resultado-calculado';
            });

            $result2 = Cache::remember('heavy_query', 60, function () use (&$executed): string {
                $executed++;

                return 'resultado-calculado';
            });

            expect($result1)->toBe('resultado-calculado');
            expect($result2)->toBe('resultado-calculado');
            // El callback solo se ejecuto una vez (el segundo hit fue cache hit)
            expect($executed)->toBe(1);
        } finally {
            tenancy()->end();
        }
    });

    it('mecanismo de tags aisla cache entre distintos tenant_ids en la misma store', function (): void {
        $tagBase  = config('tenancy.cache.tag_base');
        $tenantA  = createCacheTenant('cache-tag-iso-a');
        $tenantB  = createCacheTenant('cache-tag-iso-b');

        // Simular exactamente lo que TenantCacheManager hace internamente:
        // aplicar un tag unico por tenant a las operaciones de cache.
        $taggedA = Cache::store()->tags([$tagBase . $tenantA->id]);
        $taggedB = Cache::store()->tags([$tagBase . $tenantB->id]);

        // Ambos writes usan la misma clave pero tags distintos
        $taggedA->put('shared_key', 'valor-de-a', 60);
        $taggedB->put('shared_key', 'valor-de-b', 60);

        // Cada uno lee solo su propio valor
        expect($taggedA->get('shared_key'))->toBe('valor-de-a');
        expect($taggedB->get('shared_key'))->toBe('valor-de-b');

        // Borrar solo el tag de A no afecta a B
        $taggedA->flush();
        expect($taggedA->get('shared_key'))->toBeNull();
        expect($taggedB->get('shared_key'))->toBe('valor-de-b');
    });

    it('flush selectivo por tag de tenant no elimina cache de otro tenant', function (): void {
        $tagBase = config('tenancy.cache.tag_base');
        $idA     = 'flush-iso-a';
        $idB     = 'flush-iso-b';

        createCacheTenant($idA);
        createCacheTenant($idB);

        $taggedA = Cache::store()->tags([$tagBase . $idA]);
        $taggedB = Cache::store()->tags([$tagBase . $idB]);

        $taggedA->put('invoice_count', 10, 60);
        $taggedB->put('invoice_count', 20, 60);

        // Flush del tenant A
        Cache::tags([$tagBase . $idA])->flush();

        expect(Cache::tags([$tagBase . $idA])->get('invoice_count'))->toBeNull();
        expect(Cache::tags([$tagBase . $idB])->get('invoice_count'))->toBe(20);
    });

    it('TenantCacheManager aplica el tag correcto segun el tenant inicializado', function (): void {
        $tagBase = config('tenancy.cache.tag_base');
        $tenant  = createCacheTenant('cache-tag-verify');

        tenancy()->initialize($tenant);

        try {
            // Escribir via facade (TenantCacheManager auto-aplica el tag)
            Cache::put('tagged_key', 'tagged_value', 60);

            // Leer directamente con el tag explicioto debe encontrar el valor
            $expected = Cache::store()->tags([$tagBase . $tenant->id])->get('tagged_key');
            expect($expected)->toBe('tagged_value');
        } finally {
            tenancy()->end();
        }
    });
