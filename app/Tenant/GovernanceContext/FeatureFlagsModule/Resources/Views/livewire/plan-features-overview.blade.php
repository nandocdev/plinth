<div>
    {{-- Encabezado --}}
    <div class="mb-6">
        <flux:heading size="xl">Características del Plan</flux:heading>
        <flux:subheading>Revisa las funcionalidades habilitadas y los límites de uso de tu plan actual.
        </flux:subheading>
    </div>

    @if (!$features->hasPlan())
        <flux:callout variant="warning" icon="exclamation-triangle">
            No tienes una suscripción activa. Contacta a soporte para activar tu plan.
        </flux:callout>
    @else
        {{-- Banner de plan --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <flux:icon name="sparkles" class="size-8 text-yellow-500" />
                <div>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white">{{ $features->planName }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Plan activo</p>
                </div>
            </div>
            <flux:badge
                :color="match($features->subscriptionStatus) {
                                    'active'   => 'green',
                                    'trialing' => 'blue',
                                    'past_due' => 'yellow',
                                    default    => 'zinc',
                                }"
                size="lg">
                {{ match ($features->subscriptionStatus) {
                    'active' => 'Activo',
                    'trialing' => 'En prueba',
                    'past_due' => 'Pago pendiente',
                    default => $features->subscriptionStatus ?? 'Desconocido',
                } }}
            </flux:badge>
        </div>

        {{-- Advertencias de límites --}}
        @if ($limits->hardLimitReached)
            <flux:callout variant="danger" icon="x-circle" class="mb-6">
                <strong>Límite máximo alcanzado:</strong> {{ implode(', ', $limits->hardViolations) }}. Actualiza tu
                plan para continuar.
            </flux:callout>
        @elseif ($limits->softLimitReached)
            <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
                <strong>Acercándote al límite:</strong> {{ implode(', ', $limits->softWarnings) }}. Considera actualizar
                tu plan pronto.
            </flux:callout>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Funcionalidades booleanas --}}
            <flux:card class="p-6">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon name="check-badge" class="size-5 text-green-500" />
                    <flux:heading size="sm">Funcionalidades incluidas</flux:heading>
                </div>

                @if (empty($features->features))
                    <p class="text-sm text-zinc-400">Ninguna funcionalidad adicional en este plan.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($features->features as $flag)
                            <li class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                                <flux:icon name="check-circle" class="size-4 shrink-0 text-green-500" />
                                <span>{{ ucwords(str_replace(['_', '-'], ' ', $flag)) }}</span>
                                <flux:badge color="green" size="sm" class="ml-auto">Activo</flux:badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </flux:card>

            {{-- Límites de uso --}}
            <flux:card class="p-6">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon name="chart-bar" class="size-5 text-blue-500" />
                    <flux:heading size="sm">Límites de uso</flux:heading>
                </div>

                <div class="space-y-4">
                    {{-- Usuarios --}}
                    @if ($features->maxUsersHard !== null || $features->maxUsersSoft !== null)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Usuarios</span>
                                <div class="flex gap-2">
                                    @if ($features->maxUsersSoft !== null)
                                        <flux:badge color="yellow" size="sm">Aviso: {{ $features->maxUsersSoft }}
                                        </flux:badge>
                                    @endif
                                    @if ($features->maxUsersHard !== null)
                                        <flux:badge color="red" size="sm">Máx: {{ $features->maxUsersHard }}
                                        </flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm text-zinc-400">
                            <flux:icon name="check-circle" class="size-4 text-green-500" />
                            <span>Usuarios ilimitados</span>
                        </div>
                    @endif

                    {{-- Almacenamiento --}}
                    @if ($features->maxStorageMbHard !== null || $features->maxStorageMbSoft !== null)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Almacenamiento</span>
                                <div class="flex gap-2">
                                    @if ($features->maxStorageMbSoft !== null)
                                        <flux:badge color="yellow" size="sm">Aviso:
                                            {{ $features->maxStorageMbSoft }} MB</flux:badge>
                                    @endif
                                    @if ($features->maxStorageMbHard !== null)
                                        <flux:badge color="red" size="sm">Máx: {{ $features->maxStorageMbHard }}
                                            MB</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm text-zinc-400">
                            <flux:icon name="check-circle" class="size-4 text-green-500" />
                            <span>Almacenamiento ilimitado</span>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- Enlace a facturación --}}
        <div class="mt-6">
            <flux:button href="/billing" wire:navigate variant="ghost" icon="arrow-right">
                Ver portal de facturación
            </flux:button>
        </div>
    @endif
</div>
