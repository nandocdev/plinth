<div class="flex flex-col gap-6">
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Endpoints de Webhook</flux:heading>
            <flux:subheading>Configura las URLs externas que recibirán notificaciones de eventos del sistema.
            </flux:subheading>
        </div>
        <flux:modal.trigger name="endpoint-modal">
            <flux:button variant="primary" icon="plus" color="orange">Nuevo Endpoint</flux:button>
        </flux:modal.trigger>
    </header>

    @if (session('status'))
        <flux:card class="bg-green-50 dark:bg-green-950/20 border-green-200 dark:border-green-900/50 py-3 px-4">
            <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                <flux:icon name="check-circle" variant="micro" />
                <p class="text-sm font-medium">{{ session('status') }}</p>
            </div>
        </flux:card>
    @endif

    <flux:card class="overflow-hidden">
        <div
            class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por nombre o URL..."
                icon="magnifying-glass" class="max-w-md" />
            <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $endpoints->total() }}</b></flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre / URL</flux:table.column>
                <flux:table.column>Eventos Suscritos</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column>Creado</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($endpoints as $endpoint)
                    <flux:table.row :key="$endpoint->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $endpoint->name }}</span>
                                <span
                                    class="text-xs font-mono text-zinc-500 truncate max-w-xs">{{ $endpoint->target_url }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($endpoint->subscribed_events as $event)
                                    <flux:badge size="sm" variant="subtle" color="zinc"
                                        class="text-[10px] uppercase">
                                        {{ str_replace('tenant.', '', $event) }}
                                    </flux:badge>
                                @endforeach
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $endpoint->is_active ? 'green' : 'zinc' }}"
                                inset="top">
                                {{ $endpoint->is_active ? 'ACTIVO' : 'INACTIVO' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $endpoint->created_at->format('Y-m-d') }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="toggleEndpointStatus({{ $endpoint->id }})" variant="ghost"
                                    size="sm" icon="{{ $endpoint->is_active ? 'pause' : 'play' }}"
                                    tooltip="{{ $endpoint->is_active ? 'Desactivar' : 'Activar' }}" />

                                <flux:button variant="ghost" size="sm" icon="pencil-square" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500 italic">
                            No se encontraron endpoints configurados.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $endpoints->links() }}
        </div>
    </flux:card>

    <flux:modal name="endpoint-modal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Nuevo Endpoint de Webhook</flux:heading>
                <flux:subheading>Define el destino y los eventos para las notificaciones salientes.</flux:subheading>
            </div>

            <form wire:submit="createEndpoint" class="space-y-4">
                <flux:input wire:model="form.name" label="Nombre del Endpoint" placeholder="Ej: Zapier Integration"
                    required />
                <flux:input wire:model="form.targetUrl" label="URL de Destino"
                    placeholder="https://api.partner.com/webhooks" required />

                <flux:input wire:model="form.signingSecret" label="Secreto de Firma (opcional)"
                    placeholder="Generado automáticamente si se deja vacío" />

                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Eventos Suscritos</label>
                    <div class="grid grid-cols-2 gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @foreach ($events as $event)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="form.subscribedEvents" value="{{ $event->value }}"
                                    class="rounded border-zinc-300 text-orange-600 focus:ring-orange-500" />
                                <span>{{ $event->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <flux:checkbox wire:model="form.isActive" label="Activar inmediatamente" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Guardar Endpoint</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
