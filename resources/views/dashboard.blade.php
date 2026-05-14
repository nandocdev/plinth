<x-layouts::app :title="__('Panel de Control Central')">
    <div class="flex h-full w-full flex-1 flex-col gap-8">
        @php
            $centralUser = auth('central')->user();
        @endphp

        {{-- Alerta de Seguridad Compacta --}}
        @if ($centralUser && method_exists($centralUser, 'hasEnabledTwoFactorAuthentication') && !$centralUser->hasEnabledTwoFactorAuthentication())
            <div class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50/50 px-4 py-2 text-amber-900 dark:border-amber-900/30 dark:bg-amber-950/20 dark:text-amber-300">
                <div class="flex items-center gap-2 text-xs font-medium">
                    <flux:icon name="shield-exclamation" variant="mini" class="size-4" />
                    {{ __('Seguridad: Se recomienda activar la autenticación de dos factores (2FA).') }}
                </div>
                <flux:link href="{{ route('security.edit') }}" size="sm" class="font-semibold">{{ __('Configurar') }}</flux:link>
            </div>
        @endif

        {{-- Header Ejecutivo --}}
        <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between border-b border-zinc-100 pb-6 dark:border-zinc-800">
            <div>
                <flux:heading size="xl" level="1" class="font-bold tracking-tight">{{ __('Resumen Ejecutivo') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Visión general del ecosistema SaaS y métricas de rendimiento clave.') }}</flux:subheading>
            </div>
            
            <div class="flex items-center gap-3">
                {{-- Estado del Sistema Minimalista --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                    <span @class([
                        'size-2 rounded-full animate-pulse',
                        'bg-green-500' => $metrics->criticalErrorsLast24h === 0 && $metrics->failedJobsCount === 0,
                        'bg-red-500' => $metrics->criticalErrorsLast24h > 0,
                        'bg-amber-500' => $metrics->criticalErrorsLast24h === 0 && $metrics->failedJobsCount > 0,
                    ])></span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-600 dark:text-zinc-400">
                        @if($metrics->criticalErrorsLast24h === 0 && $metrics->failedJobsCount === 0)
                            {{ __('Sistema Estable') }}
                        @else
                            {{ __('Atención Requerida') }}
                        @endif
                    </span>
                </div>
                <flux:button href="{{ route('central.health.index') }}" variant="subtle" size="sm" icon="magnifying-glass-circle">{{ __('Salud') }}</flux:button>
            </div>
        </header>

        {{-- KPIs Financieros (Top Tier) --}}
        <section class="grid gap-6 md:grid-cols-2">
            <flux:card class="p-8 border-none bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400 mb-2">{{ __('Ingreso Recurrente (MRR)') }}</p>
                        <flux:heading size="xl" class="text-4xl font-black text-zinc-900 dark:text-white">
                            ${{ number_format($metrics->monthlyRecurringRevenueUsdCents / 100, 2) }}
                        </flux:heading>
                    </div>
                    <div class="p-3 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon name="banknotes" />
                    </div>
                </div>
                <div class="mt-6 flex items-center gap-2 text-sm text-zinc-500">
                    <flux:badge size="sm" color="indigo" variant="subtle">{{ number_format($metrics->activeSubscriptions) }}</flux:badge>
                    <span>{{ __('suscripciones activas promediadas') }}</span>
                </div>
            </flux:card>

            <flux:card class="p-8 border-none bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-zinc-500 mb-2">{{ __('Facturación del Mes') }}</p>
                        <flux:heading size="xl" class="text-4xl font-black text-zinc-900 dark:text-white">
                            ${{ number_format($metrics->monthlyRevenueUsdCents / 100, 2) }}
                        </flux:heading>
                    </div>
                    <div class="p-3 rounded-2xl bg-zinc-500/10 text-zinc-500">
                        <flux:icon name="credit-card" />
                    </div>
                </div>
                <div class="mt-6 flex items-center gap-2 text-sm text-zinc-500">
                    <flux:badge size="sm" variant="subtle">{{ number_format($metrics->monthlyPaidInvoices) }}</flux:badge>
                    <span>{{ __('facturas cobradas este periodo') }}</span>
                </div>
            </flux:card>
        </section>

        {{-- Salud del Ecosistema --}}
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card class="p-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">{{ __('Tenants Activos') }}</p>
                <div class="flex items-baseline gap-2">
                    <flux:heading size="lg" class="text-2xl font-bold">{{ number_format($metrics->activeTenants) }}</flux:heading>
                    <span class="text-xs font-semibold text-green-600">+{{ $metrics->newTenantsLast7Days }}</span>
                </div>
            </flux:card>

            <flux:card class="p-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">{{ __('Tenants en Riesgo') }}</p>
                <div class="flex items-baseline gap-2">
                    <flux:heading size="lg" @class(['text-2xl font-bold', 'text-red-500' => $metrics->atRiskTenants > 0])>
                        {{ number_format($metrics->atRiskTenants) }}
                    </flux:heading>
                    @if($metrics->atRiskTenants > 0)
                        <flux:link href="{{ route('central.tenants.index', ['filter' => 'at-risk']) }}" size="sm" class="text-[10px] font-bold uppercase text-red-500">{{ __('Revisar') }}</flux:link>
                    @endif
                </div>
            </flux:card>

            <flux:card class="p-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">{{ __('Facturas Pendientes') }}</p>
                <div class="flex items-baseline gap-2">
                    <flux:heading size="lg" class="text-2xl font-bold">{{ $openInvoices }}</flux:heading>
                    <span class="text-[10px] font-mono text-zinc-500">${{ number_format($openInvoicesAmountUsdCents / 100, 0) }}</span>
                </div>
            </flux:card>

            <flux:card class="p-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">{{ __('Suscripciones Trial') }}</p>
                <div class="flex items-baseline gap-2">
                    <flux:heading size="lg" class="text-2xl font-bold">{{ number_format($metrics->trialingSubscriptions) }}</flux:heading>
                    <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-tighter">{{ __('Potencial') }}</span>
                </div>
            </flux:card>
        </section>

        {{-- Alertas Operativas Críticas (Simplificadas) --}}
        @if ($metrics->criticalErrorsLast24h > 0 || $metrics->failedJobsCount > 0)
            <section class="grid gap-4 md:grid-cols-2">
                @if ($metrics->criticalErrorsLast24h > 0)
                    <div class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50/30 p-4 dark:border-red-900/20 dark:bg-red-950/10">
                        <div class="flex items-center gap-3">
                            <flux:icon name="exclamation-circle" class="text-red-600 size-5" />
                            <div>
                                <p class="text-xs font-bold text-red-900 dark:text-red-200">{{ $metrics->criticalErrorsLast24h }} {{ __('Errores Críticos detectados en las últimas 24h') }}</p>
                            </div>
                        </div>
                        <flux:button size="xs" variant="subtle" color="red" href="{{ route('central.logs.index', ['type' => 'error']) }}">{{ __('Ver Logs') }}</flux:button>
                    </div>
                @endif

                @if ($metrics->failedJobsCount > 0)
                    <div class="flex items-center justify-between rounded-xl border border-amber-100 bg-amber-50/30 p-4 dark:border-amber-900/20 dark:bg-amber-950/10">
                        <div class="flex items-center gap-3">
                            <flux:icon name="cpu-chip" class="text-amber-600 size-5" />
                            <div>
                                <p class="text-xs font-bold text-amber-900 dark:text-amber-200">{{ $metrics->failedJobsCount }} {{ __('Jobs en cola han fallado recientemente') }}</p>
                            </div>
                        </div>
                        <flux:button size="xs" variant="subtle" color="amber" href="/central/horizon/failed">{{ __('Gestionar') }}</flux:button>
                    </div>
                @endif
            </section>
        @endif

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Actividad del Ecosistema --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg" class="font-bold">{{ __('Actividad del Ecosistema') }}</flux:heading>
                    <flux:link href="{{ route('central.logs.index') }}" size="sm">{{ __('Ver todo') }}</flux:link>
                </div>

                <div class="rounded-2xl border border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm p-2">
                    <div class="flex flex-col gap-1">
                        @forelse ($recentActivity as $entry)
                            <div class="flex items-center gap-4 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors rounded-xl">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                    @if (str_contains($entry->description, 'Tenant'))
                                        <flux:icon name="building-office-2" variant="mini" />
                                    @elseif(str_contains($entry->description, 'User'))
                                        <flux:icon name="user" variant="mini" />
                                    @elseif($entry->log_name === 'error')
                                        <flux:icon name="bug-ant" variant="mini" class="text-red-500" />
                                    @else
                                        <flux:icon name="bolt" variant="mini" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-xs font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $entry->description }}
                                    </p>
                                    <p class="text-[10px] text-zinc-500">
                                        {{ $entry->created_at->diffForHumans() }} • {{ strtoupper($entry->log_name) }}
                                    </p>
                                </div>
                                @if ($entry->causer_id)
                                    <span class="text-[10px] text-zinc-400 font-mono">{{ __('ADM') }} #{{ $entry->causer_id }}</span>
                                @endif
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <flux:icon name="inbox" size="lg" class="mb-2 text-zinc-200" />
                                <p class="text-xs text-zinc-500">{{ __('Sin actividad reciente.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Operaciones Rápidas --}}
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="mb-4 font-bold">{{ __('Operaciones Rápidas') }}</flux:heading>
                    <div class="grid gap-2">
                        <flux:button href="{{ route('central.tenants.onboarding') }}" variant="filled" icon="plus" class="justify-start">{{ __('Nuevo Tenant') }}</flux:button>
                        <flux:button href="{{ route('central.tenants.index') }}" variant="subtle" icon="magnifying-glass" class="justify-start">{{ __('Buscar y Gestionar') }}</flux:button>
                        <flux:button href="{{ route('central.billing.invoices') }}" variant="subtle" icon="document-text" class="justify-start">{{ __('Revisar Facturación') }}</flux:button>
                        <flux:button href="{{ route('central.logs.index') }}" variant="subtle" icon="document-magnifying-glass" class="justify-start">{{ __('Logs del Sistema') }}</flux:button>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-200 mb-4">{{ __('Soporte Proactivo') }}</p>
                    <p class="text-sm leading-relaxed mb-6">{{ __('Hay :count tenants identificados en riesgo de abandono.', ['count' => $metrics->atRiskTenants]) }}</p>
                    <flux:button variant="primary" color="white" class="w-full !text-indigo-600 !font-bold" size="sm">{{ __('Ver Reporte de Churn') }}</flux:button>
                </div>
            </div>
        </div>

        {{-- Top Performance Tenants --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg" class="font-bold">{{ __('Tenants de Alto Rendimiento') }}</flux:heading>
                <span class="text-[10px] text-zinc-500 font-mono uppercase tracking-widest">{{ __('Sync: 5 min ago') }}</span>
            </div>
            
            <div class="rounded-2xl border border-zinc-100 dark:border-zinc-800 overflow-hidden bg-white dark:bg-zinc-900 shadow-sm">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="text-[10px] font-bold uppercase tracking-widest">{{ __('Nombre del Tenant') }}</flux:table.column>
                        <flux:table.column class="text-[10px] font-bold uppercase tracking-widest">{{ __('Plan') }}</flux:table.column>
                        <flux:table.column class="text-[10px] font-bold uppercase tracking-widest text-center">{{ __('Carga (Req/24h)') }}</flux:table.column>
                        <flux:table.column class="text-[10px] font-bold uppercase tracking-widest">{{ __('Estado') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        <flux:table.row>
                            <flux:table.cell class="font-semibold text-sm">Acme Corp</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="indigo" variant="subtle">Enterprise</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-center font-mono text-xs text-zinc-500">12.4k</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1.5">
                                    <span class="size-1.5 rounded-full bg-green-500"></span>
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Saludable') }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                <flux:button size="sm" variant="ghost" icon="chevron-right" />
                            </flux:table.cell>
                        </flux:table.row>
                        <flux:table.row>
                            <flux:table.cell class="font-semibold text-sm">Globex Inc</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc" variant="subtle">Growth</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-center font-mono text-xs text-zinc-500">8.1k</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1.5">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Al Límite') }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                <flux:button size="sm" variant="ghost" icon="chevron-right" />
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                </flux:table>
            </div>
        </section>

        <footer class="flex items-center justify-between border-t border-zinc-100 pt-6 pb-4 dark:border-zinc-800">
            <p class="text-[10px] text-zinc-400 italic">{{ __('Los datos se actualizan automáticamente según la política de caché operativa.') }}</p>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">© 2026 Plinth Management Console</span>
            </div>
        </footer>
    </div>
</x-layouts::app>
