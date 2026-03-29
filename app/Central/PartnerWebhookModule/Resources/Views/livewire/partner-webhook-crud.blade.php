<div class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Partner Webhooks') }}</flux:heading>
                <flux:subheading>{{ __('Gestiona webhooks salientes para partners y monitorea sus entregas.') }}
                </flux:subheading>
            </div>
        </div>

        @if (session('status'))
            <flux:text class="mt-4 text-green-600 dark:text-green-400">{{ session('status') }}</flux:text>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg">{{ __('Crear endpoint partner') }}</flux:heading>

        <form wire:submit="createEndpoint" class="mt-4 grid gap-4 md:grid-cols-2">
            <flux:input wire:model="form.name" :label="__('Nombre')" :placeholder="__('Partner Acme')" required />
            <flux:input wire:model="form.targetUrl" :label="__('URL destino')"
                :placeholder="__('https://partner.example/webhooks/plinth')" required />

            <flux:input wire:model="form.signingSecret" :label="__('Signing secret')" :placeholder="__('secret_xxx...')"
                required />

            <div>
                <label class="mb-2 block text-sm text-zinc-700 dark:text-zinc-300">{{ __('Eventos suscritos') }}</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($events as $event)
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                            <input type="checkbox" wire:model="form.subscribedEvents" value="{{ $event->value }}"
                                class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800" />
                            <span>{{ $event->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-end gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" wire:model="form.isActive"
                        class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800" />
                    {{ __('Activo') }}
                </label>
            </div>

            <div class="md:col-span-2">
                <flux:button type="submit" variant="primary">{{ __('Crear endpoint') }}</flux:button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <flux:input wire:model.live.debounce.400ms="endpointsSearch" :label="__('Buscar endpoint')"
                :placeholder="__('Nombre o URL')" class="md:max-w-sm" />
            <flux:text>{{ __('Endpoints: :count', ['count' => $endpoints->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Nombre') }}</th>
                        <th class="py-3 pr-3">{{ __('URL') }}</th>
                        <th class="py-3 pr-3">{{ __('Eventos') }}</th>
                        <th class="py-3 pr-3">{{ __('Estado') }}</th>
                        <th class="py-3 pr-3">{{ __('Entregas') }}</th>
                        <th class="py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($endpoints as $endpoint)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3 pr-3">{{ $endpoint->name }}</td>
                            <td class="py-3 pr-3 font-mono text-xs break-all">{{ $endpoint->target_url }}</td>
                            <td class="py-3 pr-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ((array) $endpoint->subscribed_events as $event)
                                        <flux:badge size="sm">{{ $event }}</flux:badge>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 pr-3">
                                @if ($endpoint->is_active)
                                    <flux:badge color="lime" variant="solid" size="sm">{{ __('Activo') }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" variant="solid" size="sm">{{ __('Inactivo') }}
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="py-3 pr-3">{{ $endpoint->deliveries_count }}</td>
                            <td class="py-3 text-right">
                                <flux:button size="sm" wire:click="toggleEndpointStatus({{ $endpoint->id }})"
                                    variant="filled">
                                    {{ $endpoint->is_active ? __('Desactivar') : __('Activar') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-zinc-500">
                                {{ __('No hay endpoints registrados.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $endpoints->links() }}
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <flux:input wire:model.live.debounce.400ms="deliveriesSearch" :label="__('Buscar entrega')"
                :placeholder="__('uuid, event, status, tenant')" class="md:max-w-sm" />
            <flux:text>{{ __('Entregas: :count', ['count' => $deliveries->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Fecha') }}</th>
                        <th class="py-3 pr-3">{{ __('Endpoint') }}</th>
                        <th class="py-3 pr-3">{{ __('Evento') }}</th>
                        <th class="py-3 pr-3">{{ __('Tenant') }}</th>
                        <th class="py-3 pr-3">{{ __('Estado') }}</th>
                        <th class="py-3 pr-3">{{ __('Intentos') }}</th>
                        <th class="py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deliveries as $delivery)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3 pr-3 font-mono text-xs">{{ $delivery->created_at?->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-3 pr-3">{{ $delivery->endpoint?->name ?? '-' }}</td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ $delivery->event }}</td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ $delivery->tenant_id ?? '-' }}</td>
                            <td class="py-3 pr-3">
                                <flux:badge size="sm" color="zinc">{{ $delivery->status }}</flux:badge>
                            </td>
                            <td class="py-3 pr-3">{{ $delivery->attempts }}/{{ $delivery->max_attempts }}</td>
                            <td class="py-3 text-right">
                                @if ($delivery->status !== 'delivered')
                                    <flux:button size="sm" variant="filled"
                                        wire:click="retryDelivery({{ $delivery->id }})">
                                        {{ __('Reintentar') }}
                                    </flux:button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-zinc-500">
                                {{ __('No hay entregas para mostrar.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>
