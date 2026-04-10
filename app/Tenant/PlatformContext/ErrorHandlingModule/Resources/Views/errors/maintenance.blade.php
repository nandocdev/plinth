<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-100 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100">
    <main class="mx-auto max-w-3xl px-4 py-12">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/15">
                <flux:icon name="wrench-screwdriver" class="size-7 text-amber-500" />
            </div>

            <flux:heading size="xl" class="mb-2">Workspace en mantenimiento</flux:heading>
            <flux:subheading>
                {{ $message ?? 'Estamos aplicando mejoras. Vuelve a intentar en unos minutos.' }}
            </flux:subheading>

            <div
                class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-left text-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p><strong>Workspace:</strong> {{ $tenantName ?? (tenant()?->brandName() ?? config('app.name')) }}</p>
                <p><strong>Tenant ID:</strong> {{ $tenantId ?? (string) (tenant()?->id ?? '-') }}</p>
                <p><strong>Estado:</strong> maintenance</p>
            </div>
        </div>
    </main>

    @fluxScripts
</body>

</html>
