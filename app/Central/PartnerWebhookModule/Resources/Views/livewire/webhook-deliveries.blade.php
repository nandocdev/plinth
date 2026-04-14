<div class="flex flex-col gap-6">
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Historial de Entregas</flux:heading>
            <flux:subheading>Registro de intentos de envío y respuestas de los servidores de destino.</flux:subheading>
        </div>
    </header>

    <flux:card class="overflow-hidden">
        <div
            class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por Evento o Endpoint ID..."
                icon="magnifying-glass" class="max-w-md" />
            <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $deliveries->total() }}</b></flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Evento / ID</flux:table.column>
                <flux:table.column>Endpoint</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column>HTTP Code</flux:table.column>
                <flux:table.column>Intentos</flux:table.column>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($deliveries as $delivery)
                    <flux:table.row :key="$delivery->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span
                                    class="font-bold text-zinc-900 dark:text-zinc-100 uppercase text-xs">{{ str_replace('tenant.', '', $delivery->event) }}</span>
                                <span class="text-[10px] font-mono text-zinc-500">{{ $delivery->uuid }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span
                                class="text-sm text-zinc-600 dark:text-zinc-400">{{ $delivery->endpoint?->name ?? 'Endpoint Eliminado' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm"
                                color="{{ match ($delivery->status) {
                                    'success' => 'green',
                                    'failed' => 'red',
                                    'pending' => 'amber',
                                    default => 'zinc',
                                } }}"
                                inset="top">
                                {{ strtoupper($delivery->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span
                                class="font-mono text-xs {{ $delivery->response_status >= 200 && $delivery->response_status < 300 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $delivery->response_status ?: '-' }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-500">{{ $delivery->attempts }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $delivery->created_at->format('Y-m-d H:i') }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end">
                                @if ($delivery->status === 'failed')
                                    <flux:button wire:click="retryDelivery({{ $delivery->id }})" variant="ghost"
                                        size="sm" icon="arrow-path" tooltip="Reintentar ahora" />
                                @endif
                                <flux:button variant="ghost" size="sm" icon="information-circle" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center text-zinc-500 italic">
                            No se han registrado intentos de entrega.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $deliveries->links() }}
        </div>
    </flux:card>
</div>
