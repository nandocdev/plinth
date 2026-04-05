<div class="mx-auto max-w-6xl space-y-8 px-4 py-8">

    {{-- Mensajes de estado --}}
    @if ($successMessage)
        <flux:callout variant="success" icon="check-circle">{{ $successMessage }}</flux:callout>
    @endif
    @if ($errorMessage)
        <flux:callout variant="danger" icon="exclamation-triangle">{{ $errorMessage }}</flux:callout>
    @endif

    {{-- Cabecera --}}
    <div>
        <flux:heading size="xl">Addons</flux:heading>
        <flux:subheading>Activa o desactiva las funcionalidades modulares de tu workspace.</flux:subheading>
    </div>

    {{-- Grupos por categoría --}}
    @foreach ($grouped as $categoryLabel => $addons)
        <section class="space-y-4">
            <flux:heading size="lg">{{ $categoryLabel }}</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($addons as $addon)
                    <flux:card class="flex flex-col gap-4">
                        {{-- Cabecera del card --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-lg
                                    {{ $addon->isActive
                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'
                                        : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    <flux:icon name="{{ $addon->icon }}" class="size-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $addon->label }}
                                    </p>
                                    @if ($addon->isActive)
                                        <flux:badge color="green" size="sm">Activo</flux:badge>
                                    @elseif ($addon->isInstalled)
                                        <flux:badge color="zinc" size="sm">Inactivo</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm" inset="top bottom">No instalado
                                        </flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <p class="grow text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $addon->description }}
                        </p>

                        {{-- Fecha instalación --}}
                        @if ($addon->installedAt)
                            <p class="text-xs text-zinc-400 dark:text-zinc-500">
                                Instalado: {{ \Carbon\Carbon::parse($addon->installedAt)->format('d/m/Y') }}
                            </p>
                        @endif

                        {{-- Botón toggle --}}
                        @can('manage', \App\Tenant\AddonsModule\Models\TenantAddon::class)
                            <flux:button wire:click="toggle('{{ $addon->slug }}')" wire:loading.attr="disabled"
                                wire:target="toggle('{{ $addon->slug }}')"
                                variant="{{ $addon->isActive ? 'ghost' : 'primary' }}" size="sm" class="w-full">
                                <span wire:loading.remove wire:target="toggle('{{ $addon->slug }}')">
                                    {{ $addon->isActive ? 'Desactivar' : 'Activar' }}
                                </span>
                                <span wire:loading wire:target="toggle('{{ $addon->slug }}')">
                                    Procesando…
                                </span>
                            </flux:button>
                        @else
                            <flux:button variant="ghost" size="sm" class="w-full" disabled>
                                {{ $addon->isActive ? 'Activo' : 'Inactivo' }}
                            </flux:button>
                        @endcan
                    </flux:card>
                @endforeach
            </div>
        </section>
    @endforeach

</div>
