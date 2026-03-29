<div class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="xl">{{ __('Central audit log') }}</flux:heading>
        <flux:subheading>{{ __('Historial completo de acciones ejecutadas en el panel central.') }}</flux:subheading>

        <div class="mt-6 grid gap-4 md:grid-cols-5">
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
                <label for="logs-event" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Evento') }}
                </label>
                <select id="logs-event" wire:model.live="filterForm.event"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">{{ __('Todos') }}</option>
                    <option value="get.request">get.request</option>
                    <option value="post.request">post.request</option>
                    <option value="put.request">put.request</option>
                    <option value="patch.request">patch.request</option>
                    <option value="delete.request">delete.request</option>
                    <option value="livewire.action">livewire.action</option>
                </select>
            </div>

            <div>
                <label for="logs-causer" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Admin') }}
                </label>
                <select id="logs-causer" wire:model.live="filterForm.causerId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach ($adminOptions as $admin)
                        <option value="{{ $admin['id'] }}">{{ $admin['name'] }} ({{ $admin['email'] }})</option>
                    @endforeach
                </select>
            </div>

            <flux:input wire:model.live.debounce.400ms="filterForm.search" :label="__('Buscar')"
                :placeholder="__('descripcion, evento, subject...')" />

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
                        <th class="py-3 pr-3">{{ __('Admin') }}</th>
                        <th class="py-3 pr-3">{{ __('Evento') }}</th>
                        <th class="py-3 pr-3">{{ __('Ruta') }}</th>
                        <th class="py-3 pr-3">{{ __('Tenant') }}</th>
                        <th class="py-3 pr-3">{{ __('Descripción') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-zinc-100 align-top dark:border-zinc-800">
                            <td class="py-3 pr-3 font-mono text-xs">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 pr-3">
                                <div class="text-xs text-zinc-700 dark:text-zinc-300">
                                    {{ $log->causer?->name ?? __('Sistema') }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $log->causer?->email ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ $log->event ?? '-' }}</td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ data_get($log->properties, 'route_name', '-') }}
                            </td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ data_get($log->properties, 'tenant_id', '-') }}
                            </td>
                            <td class="py-3 pr-3 break-all font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                {{ $log->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-zinc-500">
                                {{ __('No hay registros de auditoría para los filtros seleccionados.') }}
                            </td>
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
