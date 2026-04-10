<div class="space-y-6">
    <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <flux:badge color="zinc" size="sm">Workspace Tenant</flux:badge>
                <flux:heading size="xl">{{ __('Bienvenido, :name', ['name' => $dashboard->userName]) }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Ya estás dentro del workspace :tenant sobre el dominio :domain.', ['tenant' => $dashboard->tenantName, 'domain' => $dashboard->tenantDomain]) }}
                </flux:subheading>
            </div>

            @if ($dashboard->logoUrl)
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <img src="{{ $dashboard->logoUrl }}" alt="{{ $dashboard->tenantName }}"
                        class="h-14 w-auto rounded-md" />
                </div>
            @endif
        </div>
    </section>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <flux:card class="p-5">
            <p class="text-xs uppercase tracking-wide text-zinc-500">Tenant ID</p>
            <p class="mt-2 font-mono text-sm text-zinc-900 dark:text-white">{{ $dashboard->tenantId }}</p>
        </flux:card>

        <flux:card class="p-5">
            <p class="text-xs uppercase tracking-wide text-zinc-500">Región</p>
            <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ $dashboard->tenantRegion }}</p>
        </flux:card>

        <flux:card class="p-5">
            <p class="text-xs uppercase tracking-wide text-zinc-500">Usuario actual</p>
            <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ $dashboard->userEmail }}</p>
        </flux:card>

        <flux:card class="p-5">
            <p class="text-xs uppercase tracking-wide text-zinc-500">Dominio activo</p>
            <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ $dashboard->tenantDomain }}</p>
        </flux:card>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        <flux:card class="p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">{{ __('Resumen del workspace') }}</flux:heading>
                    <flux:text class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                        {{ __('Este dashboard inicial te confirma el aislamiento tenant, el usuario autenticado y la identidad visual aplicada desde central.') }}
                    </flux:text>
                </div>
                <div class="flex gap-2">
                    <a href="/billing" wire:navigate>
                        <flux:button variant="primary" icon="credit-card">{{ __('Ir a facturación') }}</flux:button>
                    </a>
                </div>
            </div>
        </flux:card>

        <flux:card class="p-6">
            <flux:heading size="lg">{{ __('Branding activo') }}</flux:heading>
            <div class="mt-4 space-y-4">
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="text-xs uppercase tracking-wide text-zinc-500">Primary Color</p>
                    <p class="mt-2 font-mono text-sm" style="color: {{ $dashboard->primaryColor }}">
                        {{ $dashboard->primaryColor }}</p>
                </div>

                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="text-xs uppercase tracking-wide text-zinc-500">Secondary Color</p>
                    <p class="mt-2 font-mono text-sm" style="color: {{ $dashboard->secondaryColor }}">
                        {{ $dashboard->secondaryColor }}</p>
                </div>
            </div>
        </flux:card>
    </div>
</div>
