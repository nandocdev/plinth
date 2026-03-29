@php
    /** @var \App\Central\TenantProvisioningModule\Models\Tenant $tenant */
    $tenant = tenancy()->tenant;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => __('Tenant Home')])
</head>

<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <main class="mx-auto max-w-4xl space-y-6 px-4 py-8">
        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-2xl font-semibold" style="color: var(--brand-primary)">{{ $tenant->brandName() }}</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Workspace tenant activo en :tenant con dominio :domain', ['tenant' => $tenant->id, 'domain' => request()->getHost()]) }}
            </p>

            <div class="mt-6 grid gap-3 md:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 p-4 text-sm dark:border-zinc-700">
                    <div class="text-zinc-500">{{ __('Primary Color') }}</div>
                    <div class="mt-2 font-mono" style="color: var(--brand-primary)">{{ $tenant->primaryColor() }}</div>
                </div>

                <div class="rounded-lg border border-zinc-200 p-4 text-sm dark:border-zinc-700">
                    <div class="text-zinc-500">{{ __('Secondary Color') }}</div>
                    <div class="mt-2 font-mono" style="color: var(--brand-secondary)">{{ $tenant->secondaryColor() }}</div>
                </div>
            </div>

            @if ($tenant->logoUrl())
                <div class="mt-6">
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Configured Logo') }}</p>
                    <img src="{{ $tenant->logoUrl() }}" alt="{{ $tenant->brandName() }}"
                        class="mt-2 h-14 w-auto rounded-md border border-zinc-200 p-2 dark:border-zinc-700" />
                </div>
            @endif
        </section>
    </main>
</body>

</html>
