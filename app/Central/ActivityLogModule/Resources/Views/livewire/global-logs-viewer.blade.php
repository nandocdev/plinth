<div class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="xl">{{ __('Global logs') }}</flux:heading>
        <flux:subheading>{{ __('Visualiza eventos globales y filtra por tenant para investigar incidentes.') }}</flux:subheading>

        <div class="mt-6 grid gap-4 md:grid-cols-4">
            <div>
                <label for="logs-tenant" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Tenant') }}
                </label>
                <select id="logs-tenant" wire:model.live="filterForm.tenantId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach ($tenantOptions as $tenant)
                        <option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="logs-level" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Nivel') }}
                </label>
                <select id="logs-level" wire:model.live="filterForm.level"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">{{ __('Todos') }}</option>
                    <option value="debug">debug</option>
                    <option value="info">info</option>
                    <option value="notice">notice</option>
                    <option value="warning">warning</option>
                    <option value="error">error</option>
                    <option value="critical">critical</option>
                    <option value="alert">alert</option>
                    <option value="emergency">emergency</option>
                </select>
            </div>

            <flux:input wire:model.live.debounce.400ms="filterForm.search" :label="__('Buscar')"
                :placeholder="__('tenant_id, mensaje, excepcion...')" />

            <div>
                <label for="logs-per-page" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Registros por pagina') }}
                </label>
                <select id="logs-per-page" wire:model.live="filterForm.perPage"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <flux:button wire:click="clearFilters" variant="filled">{{ __('Limpiar filtros') }}</flux:button>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
            <flux:text>{{ __('Resultados: :count', ['count' => $logs->total()]) }}</flux:text>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Fecha') }}</th>
                        <th class="py-3 pr-3">{{ __('Nivel') }}</th>
                        <th class="py-3 pr-3">{{ __('Tenant') }}</th>
                        <th class="py-3 pr-3">{{ __('Mensaje') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-zinc-100 align-top dark:border-zinc-800">
                            <td class="py-3 pr-3 font-mono text-xs">{{ $log->timestamp }}</td>
                            <td class="py-3 pr-3">
                                <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $log->level }}
                                </span>
                            </td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ $log->tenantId ?? '-' }}</td>
                            <td class="py-3 pr-3 break-all font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                {{ $log->message }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-zinc-500">{{ __('No se encontraron logs para el filtro actual.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
