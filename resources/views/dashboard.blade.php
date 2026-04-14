<x-layouts::app :title="__('Dashboard Operativo')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        @php
            $centralUser = auth('central')->user();
        @endphp

        @if (
            $centralUser &&
                method_exists($centralUser, 'hasEnabledTwoFactorAuthentication') &&
                !$centralUser->hasEnabledTwoFactorAuthentication())
            <div
                class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-200">
                <p class="text-sm font-medium">Recomendación de seguridad: activa 2FA para proteger tu cuenta.</p>
                <a href="{{ route('security.edit') }}" class="mt-2 inline-flex text-sm underline">Configurar 2FA ahora</a>
            </div>
        @endif

        {{-- Header Operativo --}}
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <flux:heading size="xl" level="1">Estado del Sistema</flux:heading>
                <flux:subheading>Panel de control central y señales operativas críticas.</flux:subheading>
            </div>
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('central.tenants.onboarding') }}" variant="primary" icon="plus"
                    color="orange">
                    Crear Tenant
                </flux:button>
                <flux:button href="{{ route('central.health.index') }}" variant="ghost" icon="beaker">
                    Infra Salud
                </flux:button>
            </div>
        </header>

        {{-- Alertas Críticas (Solo si hay problemas) --}}
        @if ($metrics->criticalErrorsLast24h > 0 || $metrics->failedJobsCount > 0)
            <section class="grid gap-4 md:grid-cols-2">
                @if ($metrics->criticalErrorsLast24h > 0)
                    <div
                        class="flex items-center justify-between rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/20">
                        <div class="flex items-center gap-3">
                            <flux:icon name="exclamation-triangle" class="text-red-600" />
                            <div>
                                <p class="text-sm font-bold text-red-900 dark:text-red-200">
                                    {{ $metrics->criticalErrorsLast24h }} Errores Críticos (24h)</p>
                                <p class="text-xs text-red-700 dark:text-red-400">Se han detectado excepciones no
                                    controladas en el sistema central.</p>
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" color="red"
                            href="{{ route('central.logs.index', ['type' => 'error']) }}">Ver Errores</flux:button>
                    </div>
                @endif

                @if ($metrics->failedJobsCount > 0)
                    <div
                        class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                        <div class="flex items-center gap-3">
                            <flux:icon name="cpu-chip" class="text-amber-600" />
                            <div>
                                <p class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                    {{ $metrics->failedJobsCount }} Jobs Fallidos</p>
                                <p class="text-xs text-amber-700 dark:text-amber-400">Hay procesos en segundo plano que
                                    requieren reintento manual.</p>
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" color="amber" href="/central/horizon/failed">
                            Gestionar Cola</flux:button>
                    </div>
                @endif
            </section>
        @endif

        {{-- Métricas HUD --}}
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card class="flex flex-col gap-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Tenants Activos</p>
                <div class="flex items-end gap-2">
                    <flux:heading size="xl">{{ number_format($metrics->activeTenants) }}</flux:heading>
                    <flux:badge size="sm" color="green">+{{ $metrics->newTenantsLast7Days }}</flux:badge>
                </div>
                <p class="text-xs text-zinc-400">en los últimos 7 días</p>
            </flux:card>

            <flux:card class="flex flex-col gap-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Tenants en Riesgo</p>
                <flux:heading size="xl" class="{{ $metrics->atRiskTenants > 5 ? 'text-red-600' : '' }}">
                    {{ number_format($metrics->atRiskTenants) }}</flux:heading>
                <flux:link href="{{ route('central.tenants.index', ['filter' => 'at-risk']) }}" size="sm"
                    class="text-orange-600">Ver lista de riesgo</flux:link>
            </flux:card>

            <flux:card class="flex flex-col gap-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">MRR Estimado</p>
                <flux:heading size="xl">${{ number_format($metrics->monthlyRecurringRevenueUsdCents / 100, 2) }}
                </flux:heading>
                <p class="text-xs text-zinc-400">{{ number_format($metrics->activeSubscriptions) }} suscripciones
                    activas</p>
            </flux:card>

            <flux:card class="flex flex-col gap-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Revenue Mensual</p>
                <flux:heading size="xl">${{ number_format($metrics->monthlyRevenueUsdCents / 100, 2) }}
                </flux:heading>
                <p class="text-xs text-zinc-400">{{ number_format($metrics->monthlyPaidInvoices) }} facturas cobradas
                </p>
            </flux:card>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Feed de Actividad Real --}}
            <div class="flex flex-col gap-4 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Actividad del Ecosistema</flux:heading>
                    <flux:button variant="ghost" size="sm" href="{{ route('central.logs.index') }}">Ver historial
                        completo</flux:button>
                </div>

                <div class="space-y-2">
                    @forelse ($recentActivity as $entry)
                        <div
                            class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                @if (str_contains($entry->description, 'Tenant'))
                                    <flux:icon name="building-office-2" />
                                @elseif(str_contains($entry->description, 'User'))
                                    <flux:icon name="user" />
                                @elseif($entry->log_name === 'error')
                                    <flux:icon name="bug-ant" class="text-red-500" />
                                @else
                                    <flux:icon name="bolt" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $entry->description }}</p>
                                <p class="text-xs text-zinc-500">
                                    {{ $entry->created_at->diffForHumans() }} • {{ strtoupper($entry->log_name) }}
                                </p>
                            </div>
                            @if ($entry->causer_id)
                                <flux:badge size="sm" variant="outline">Admin #{{ $entry->causer_id }}
                                </flux:badge>
                            @endif
                        </div>
                    @empty
                        <flux:card class="flex flex-col items-center justify-center py-10 text-center">
                            <flux:icon name="inbox" size="lg" class="mb-2 text-zinc-300" />
                            <p class="text-sm text-zinc-500">No se detectaron eventos operativos recientes.</p>
                        </flux:card>
                    @endforelse
                </div>
            </div>

            {{-- Acciones Rápidas y Operativa --}}
            <div class="flex flex-col gap-6">
                <section>
                    <flux:heading size="lg" class="mb-4">Acciones Rápidas</flux:heading>
                    <div class="grid gap-2">
                        <flux:button icon="magnifying-glass" href="{{ route('central.tenants.index') }}"
                            variant="filled" class="justify-start">Impersonar Tenant</flux:button>
                        <flux:button icon="no-symbol" href="{{ route('central.tenants.index') }}" variant="filled"
                            class="justify-start">Suspender Cuenta</flux:button>
                        <flux:button icon="document-magnifying-glass" href="{{ route('central.logs.index') }}"
                            variant="filled" class="justify-start">Ver Logs Globales</flux:button>
                        <flux:button icon="shield-check" href="{{ route('security.edit') }}" variant="filled"
                            class="justify-start">Auditoría de Acceso</flux:button>
                    </div>
                </section>

                <section>
                    <flux:heading size="lg" class="mb-4">Atención de Facturación</flux:heading>
                    <flux:card class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm text-zinc-500">Facturas Pendientes</p>
                            <flux:badge color="red" size="sm">{{ $openInvoices }}</flux:badge>
                        </div>
                        <p class="text-2xl font-bold">${{ number_format($openInvoicesAmountUsdCents / 100, 2) }} <span
                                class="text-xs font-normal text-zinc-500 uppercase tracking-tighter">USD</span></p>
                        <flux:button href="{{ route('central.billing.invoices') }}" variant="ghost" size="sm"
                            class="mt-4 w-full">Ir a Billing</flux:button>
                    </flux:card>
                </section>
            </div>
        </div>

        {{-- Top Tenants --}}
        <section class="mt-4">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Top Tenants (Uso de Recursos)</flux:heading>
                <span class="text-xs text-zinc-500 italic">Agregación batch calculada hace 5 min.</span>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tenant</flux:table.column>
                    <flux:table.column>Plan</flux:table.column>
                    <flux:table.column>Actividad (Req/24h)</flux:table.column>
                    <flux:table.column>Estado</flux:column>
                        <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    <flux:table.row>
                        <flux:table.cell class="font-medium">Acme Corp</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="blue">Enterprise</flux:badge>
                            </flux:cell>
                            <flux:table.cell>12.4k</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="green">Saludable</flux:badge>
                                </flux:cell>
                                <flux:table.cell>
                                    <flux:button size="sm" variant="ghost" icon="chevron-right" />
                                </flux:table.cell>
                    </flux:table.row>
                    <flux:table.row>
                        <flux:table.cell class="font-medium">Globex Inc</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="orange">Pro</flux:badge>
                            </flux:cell>
                            <flux:table.cell>8.1k</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="amber">Al Límite</flux:badge>
                                </flux:cell>
                                <flux:table.cell>
                                    <flux:button size="sm" variant="ghost" icon="chevron-right" />
                                </flux:table.cell>
                    </flux:table.row>
                </flux:table.rows>
            </flux:table>
        </section>

        <footer class="flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
            <p class="text-xs text-zinc-500 italic">Los datos se actualizan automáticamente cada 5 minutos.</p>
            <div class="flex items-center gap-2">
                <span class="inline-block h-2 w-2 rounded-full bg-green-500"></span>
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Sistema Operativo</span>
            </div>
        </footer>
    </div>
</x-layouts::app>
