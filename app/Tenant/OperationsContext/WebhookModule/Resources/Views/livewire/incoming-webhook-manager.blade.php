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
            <flux:heading size="xl">Webhooks Entrantes</flux:heading>
            <flux:subheading>
                Genera tokens para que servicios externos envíen datos a tu tenant mediante POST.
            </flux:subheading>
        </div>
        @unless ($showForm)
            <flux:button wire:click="openCreate" variant="primary" icon="plus">
                Nuevo token
            </flux:button>
        @endunless
    </div>

    {{-- Formulario nuevo token --}}
    @if ($showForm)
        <flux:card class="space-y-4">
            <flux:heading size="lg">Nuevo token de recepción</flux:heading>

            <form wire:submit="create" class="space-y-4">
                <flux:field>
                    <flux:label>Nombre del token</flux:label>
                    <flux:input wire:model="form.name" placeholder="Ej: Stripe Events, GitHub CI..." />
                    <flux:error name="form.name" />
                </flux:field>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">Crear token</flux:button>
                    <flux:button type="button" wire:click="cancel" variant="ghost">Cancelar</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    {{-- Lista de tokens --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">Tokens activos</flux:heading>

        @if ($tokens->isEmpty())
            <p class="py-8 text-center text-sm text-zinc-500">
                No hay tokens creados. Genera uno para recibir webhooks externos.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <th class="pb-3 pr-4">Nombre</th>
                            <th class="pb-3 pr-4">URL de recepción</th>
                            <th class="pb-3 pr-4">Estado</th>
                            <th class="pb-3 pr-4">Recepciones</th>
                            <th class="pb-3 pr-4">Última recepción</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($tokens as $token)
                            <tr wire:key="token-{{ $token->id }}">
                                <td class="py-3 pr-4 font-medium">{{ $token->name }}</td>
                                <td class="py-3 pr-4">
                                    @if ($token->is_active)
                                        <div class="flex max-w-sm items-center gap-2">
                                            <code
                                                class="min-w-0 flex-1 truncate rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                                {{ url('/webhooks/receive/' . $token->token) }}
                                            </code>
                                            <flux:button size="sm" variant="ghost" icon="clipboard"
                                                x-on:click="navigator.clipboard.writeText('{{ url('/webhooks/receive/' . $token->token) }}')" />
                                        </div>
                                    @else
                                        <span class="text-zinc-400 italic">Revocado</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if ($token->is_active)
                                        <flux:badge color="green" size="sm">Activo</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Revocado</flux:badge>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-zinc-500">{{ $token->logs_count }}</td>
                                <td class="py-3 pr-4 text-zinc-500">
                                    {{ $token->last_used_at?->diffForHumans() ?? 'Nunca' }}
                                </td>
                                <td class="py-3">
                                    @if ($token->is_active)
                                        <flux:button size="sm" variant="ghost"
                                            class="text-red-600 hover:text-red-700"
                                            wire:click="revoke({{ $token->id }})"
                                            wire:confirm="¿Revocar este token? Los servicios que lo usen dejarán de poder enviar datos.">
                                            Revocar
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $tokens->links() }}
        @endif
    </flux:card>

    {{-- Historial de recepciones --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">Historial de recepciones</flux:heading>

        @if ($logs->isEmpty())
            <p class="py-8 text-center text-sm text-zinc-500">Ningún webhook entrante recibido aún.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <th class="pb-3 pr-4">Token</th>
                            <th class="pb-3 pr-4">Estado</th>
                            <th class="pb-3 pr-4">IP origen</th>
                            <th class="pb-3 pr-4">Código</th>
                            <th class="pb-3 pr-4">Recibido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($logs as $log)
                            <tr wire:key="log-{{ $log->id }}">
                                <td class="py-3 pr-4 text-zinc-600">{{ $log->token?->name ?? '—' }}</td>
                                <td class="py-3 pr-4">
                                    @php $statusColors = ['received' => 'blue', 'processed' => 'green', 'failed' => 'red']; @endphp
                                    <flux:badge color="{{ $statusColors[$log->status] ?? 'zinc' }}" size="sm">
                                        {{ $log->status }}
                                    </flux:badge>
                                </td>
                                <td class="py-3 pr-4 font-mono text-zinc-500 text-xs">{{ $log->source_ip ?? '—' }}</td>
                                <td class="py-3 pr-4 text-zinc-500">{{ $log->response_status }}</td>
                                <td class="py-3 pr-4 text-zinc-500">{{ $log->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        @endif
    </flux:card>
</div>
