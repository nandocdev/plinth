<div class="mx-auto max-w-6xl space-y-6 px-4 py-8">

    {{-- Mensajes de estado --}}
    @if ($successMessage)
        <flux:callout variant="success" icon="check-circle">{{ $successMessage }}</flux:callout>
    @endif
    @if ($errorMessage)
        <flux:callout variant="danger" icon="exclamation-triangle">{{ $errorMessage }}</flux:callout>
    @endif

    {{-- Cabecera --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Webhooks Salientes</flux:heading>
            <flux:subheading>Gestiona los endpoints que recibirán notificaciones de eventos del tenant.
            </flux:subheading>
        </div>
        @unless ($showForm)
            <flux:button wire:click="openCreate" variant="primary" icon="plus">
                Nuevo endpoint
            </flux:button>
        @endunless
    </div>

    {{-- Formulario crear / editar --}}
    @if ($showForm)
        <flux:card class="space-y-4">
            <flux:heading size="lg">
                {{ $form->endpointId ? 'Editar endpoint' : 'Nuevo endpoint' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Nombre</flux:label>
                        <flux:input wire:model="form.name" placeholder="Mi servicio externo" />
                        <flux:error name="form.name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>URL destino</flux:label>
                        <flux:input wire:model="form.targetUrl" type="url"
                            placeholder="https://ejemplo.com/webhook" />
                        <flux:error name="form.targetUrl" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Máximo de reintentos</flux:label>
                        <flux:input wire:model="form.maxAttempts" type="number" min="1" max="10" />
                        <flux:error name="form.maxAttempts" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Estado</flux:label>
                        <div class="flex items-center gap-3 pt-2">
                            <flux:switch wire:model="form.isActive" />
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $form->isActive ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Eventos suscritos</flux:label>
                    <flux:error name="form.subscribedEvents" />
                    <div class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-3">
                        @foreach ($events as $event)
                            <label
                                class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                <input type="checkbox" wire:model="form.subscribedEvents" value="{{ $event->value }}"
                                    class="rounded border-zinc-300 text-blue-600" />
                                <span>{{ $event->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </flux:field>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">
                        {{ $form->endpointId ? 'Actualizar' : 'Crear endpoint' }}
                    </flux:button>
                    <flux:button type="button" wire:click="cancel" variant="ghost">
                        Cancelar
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    {{-- Lista de endpoints --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">Endpoints registrados</flux:heading>

        @if ($endpoints->isEmpty())
            <p class="py-8 text-center text-sm text-zinc-500">No hay endpoints registrados. Crea el primero.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <th class="pb-3 pr-4">Nombre</th>
                            <th class="pb-3 pr-4">URL</th>
                            <th class="pb-3 pr-4">Eventos</th>
                            <th class="pb-3 pr-4">Entregas</th>
                            <th class="pb-3 pr-4">Estado</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($endpoints as $endpoint)
                            <tr wire:key="endpoint-{{ $endpoint->id }}" class="group">
                                <td class="py-3 pr-4 font-medium">{{ $endpoint->name }}</td>
                                <td class="py-3 pr-4 max-w-xs truncate text-zinc-500">
                                    <span title="{{ $endpoint->target_url }}">{{ $endpoint->target_url }}</span>
                                </td>
                                <td class="py-3 pr-4">
                                    <flux:badge variant="outline" size="sm">
                                        {{ count((array) $endpoint->subscribed_events) }} evento(s)
                                    </flux:badge>
                                </td>
                                <td class="py-3 pr-4 text-zinc-500">{{ $endpoint->deliveries_count }}</td>
                                <td class="py-3 pr-4">
                                    @if ($endpoint->is_active)
                                        <flux:badge color="green" size="sm">Activo</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Inactivo</flux:badge>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <flux:button size="sm" variant="ghost"
                                            wire:click="openEdit({{ $endpoint->id }})">
                                            Editar
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost"
                                            class="text-red-600 hover:text-red-700"
                                            wire:click="delete({{ $endpoint->id }})"
                                            wire:confirm="¿Eliminar este endpoint y todo su historial?">
                                            Eliminar
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $endpoints->links() }}
        @endif
    </flux:card>

    {{-- Historial de entregas --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">Historial de entregas</flux:heading>

        @if ($deliveries->isEmpty())
            <p class="py-8 text-center text-sm text-zinc-500">No hay entregas registradas aún.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <th class="pb-3 pr-4">Endpoint</th>
                            <th class="pb-3 pr-4">Evento</th>
                            <th class="pb-3 pr-4">Estado</th>
                            <th class="pb-3 pr-4">Código HTTP</th>
                            <th class="pb-3 pr-4">Intentos</th>
                            <th class="pb-3 pr-4">Fecha</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($deliveries as $delivery)
                            <tr wire:key="delivery-{{ $delivery->id }}">
                                <td class="py-3 pr-4 text-zinc-600">{{ $delivery->endpoint?->name ?? '—' }}</td>
                                <td class="py-3 pr-4">
                                    <code
                                        class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800">{{ $delivery->event }}</code>
                                </td>
                                <td class="py-3 pr-4">
                                    @php $statusColors = ['delivered' => 'green', 'failed' => 'red', 'queued' => 'yellow', 'processing' => 'blue']; @endphp
                                    <flux:badge color="{{ $statusColors[$delivery->status] ?? 'zinc' }}"
                                        size="sm">
                                        {{ $delivery->status }}
                                    </flux:badge>
                                </td>
                                <td class="py-3 pr-4 text-zinc-500">{{ $delivery->response_status ?? '—' }}</td>
                                <td class="py-3 pr-4 text-zinc-500">
                                    {{ $delivery->attempts }}/{{ $delivery->max_attempts }}</td>
                                <td class="py-3 pr-4 text-zinc-500">{{ $delivery->created_at?->diffForHumans() }}</td>
                                <td class="py-3">
                                    @if ($delivery->status === 'failed')
                                        <flux:button size="sm" variant="ghost"
                                            wire:click="retry({{ $delivery->id }})">
                                            Reintentar
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $deliveries->links() }}
        @endif
    </flux:card>
</div>
