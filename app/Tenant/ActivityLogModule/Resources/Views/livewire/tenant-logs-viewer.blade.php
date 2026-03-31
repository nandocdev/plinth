<section class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Activity log del tenant</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            Historial de acciones del workspace actual, aislado por tenant.
        </p>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-4">
            <flux:input wire:model.live.debounce.400ms="filterForm.search" label="Buscar"
                placeholder="evento o descripción" />

            <flux:select wire:model.live="filterForm.event" label="Evento">
                <flux:select.option value="">Todos</flux:select.option>
                <flux:select.option value="get.request">GET request</flux:select.option>
                <flux:select.option value="post.request">POST request</flux:select.option>
                <flux:select.option value="put.request">PUT request</flux:select.option>
                <flux:select.option value="patch.request">PATCH request</flux:select.option>
                <flux:select.option value="delete.request">DELETE request</flux:select.option>
                <flux:select.option value="livewire.action">Livewire action</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filterForm.perPage" label="Resultados por página">
                <flux:select.option value="20">20</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
                <flux:select.option value="100">100</flux:select.option>
            </flux:select>

            <div class="flex items-end">
                <flux:button variant="ghost" wire:click="clearFilters">Limpiar filtros</flux:button>
            </div>
        </div>
    </div>

    <div
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Fecha</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Evento</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Descripción</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-zinc-100 dark:border-zinc-800">
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ optional($log->created_at)->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="zinc" size="sm">{{ $log->event ?? 'n/a' }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $log->causer?->email ?? 'sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                No hay eventos para este tenant con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
            {{ $logs->links() }}
        </div>
    </div>
</section>
