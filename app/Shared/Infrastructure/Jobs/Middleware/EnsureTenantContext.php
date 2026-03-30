<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Jobs\Middleware;

use Closure;
use RuntimeException;

final class EnsureTenantContext
{
    public function handle(object $job, Closure $next): mixed
    {
        if (! tenancy()->initialized || tenant() === null) {
            throw new RuntimeException(sprintf(
                'El job tenant-aware [%s] se ejecuto sin contexto tenant inicializado.',
                $job::class,
            ));
        }

        return $next($job);
    }
}
