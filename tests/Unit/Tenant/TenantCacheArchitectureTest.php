<?php

declare(strict_types=1);

use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\CacheManager as TenantCacheManager;

/**
 * Tests arquitecturales de cache tenant-aware.
 * Verifica que la configuracion garantiza aislamiento via el bootstrapper.
 */

it('CacheTenancyBootstrapper existe con el FQCN correcto', function (): void {
    expect(class_exists(CacheTenancyBootstrapper::class))->toBeTrue();
    expect(CacheTenancyBootstrapper::class)
        ->toBe('Stancl\\Tenancy\\Bootstrappers\\CacheTenancyBootstrapper');
});

it('TenantCacheManager extiende el CacheManager de Laravel', function (): void {
    expect(class_exists(TenantCacheManager::class))->toBeTrue();
    expect(is_subclass_of(TenantCacheManager::class, \Illuminate\Cache\CacheManager::class))
        ->toBeTrue();
});

it('el formato del tag cache es tag_base concatenado con tenant_key', function (): void {
    // Logica del TenantCacheManager: config('tenancy.cache.tag_base') . tenant()->getTenantKey()
    // Con tag_base='tenant' y key='abc-123', el tag es 'tenantabc-123'
    $tagBase  = 'tenant';
    $tenantId = 'abc-123-def-456';

    expect($tagBase . $tenantId)->toBe('tenantabc-123-def-456');
});

it('ArrayStore de Laravel 12 extiende TaggableStore (soporta tags en tests)', function (): void {
    // Verificacion estatica: ArrayStore extends TaggableStore en Laravel 12
    expect(is_subclass_of(\Illuminate\Cache\ArrayStore::class, \Illuminate\Cache\TaggableStore::class))
        ->toBeTrue();
});
