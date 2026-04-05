<div class="mx-auto max-w-7xl space-y-8 px-4 py-8">

    {{-- Cabecera --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">Analytics</flux:heading>
            <flux:subheading>
                Métricas consolidadas del tenant. Última snapshot:
                <strong>{{ $summary->lastSnapshotAt ?? '—' }}</strong>
            </flux:subheading>
        </div>

        @can('collectMetrics', \App\Tenant\ReportingModule\Models\TenantMetricSnapshot::class)
            <flux:button wire:click="collectNow" variant="primary" icon="arrow-path" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="collectNow">Recolectar métricas ahora</span>
                <span wire:loading wire:target="collectNow">Procesando…</span>
            </flux:button>
        @endcan
    </div>

    @if ($collectingMetrics)
        <flux:callout variant="success" icon="check-circle">
            Job de recolección despachado. Las métricas se actualizarán en breve.
        </flux:callout>
    @endif

    {{-- KPI Summary cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

        <flux:card>
            <flux:subheading>Usuarios totales</flux:subheading>
            <p class="mt-1 text-3xl font-bold">{{ number_format($summary->usersTotal) }}</p>
        </flux:card>

        <flux:card>
            <flux:subheading>Activos este mes</flux:subheading>
            <p class="mt-1 text-3xl font-bold">{{ number_format($summary->usersActiveMonth) }}</p>
        </flux:card>

        <flux:card>
            <flux:subheading>Logins hoy</flux:subheading>
            <p class="mt-1 text-3xl font-bold">{{ number_format($summary->loginsDay) }}</p>
        </flux:card>

        <flux:card>
            <flux:subheading>Almacenamiento</flux:subheading>
            <p class="mt-1 text-3xl font-bold">{{ number_format($summary->filesStorageMb, 2) }} <span
                    class="text-sm font-normal text-gray-500">MB</span></p>
        </flux:card>

        <flux:card>
            <flux:subheading>Entradas activity log</flux:subheading>
            <p class="mt-1 text-3xl font-bold">{{ number_format($summary->activityLogEntries) }}</p>
        </flux:card>

        <flux:card>
            <flux:subheading>Webhooks enviados</flux:subheading>
            <p class="mt-1 text-3xl font-bold text-green-600">{{ number_format($summary->webhookDeliveriesSuccess) }}
            </p>
        </flux:card>

        <flux:card>
            <flux:subheading>Webhooks fallidos</flux:subheading>
            <p class="mt-1 text-3xl font-bold text-red-600">{{ number_format($summary->webhookDeliveriesFailed) }}</p>
        </flux:card>

        <flux:card>
            <flux:subheading>Solicitudes API hoy</flux:subheading>
            <p class="mt-1 text-3xl font-bold">{{ number_format($summary->apiRequestsDay) }}</p>
        </flux:card>

    </div>

    {{-- Filtro de periodo --}}
    <flux:card class="space-y-4">
        <flux:heading size="lg">Serie temporal</flux:heading>

        <form wire:submit="applyPeriod" class="flex flex-wrap items-end gap-4">
            <flux:field>
                <flux:label>Desde</flux:label>
                <flux:input type="date" wire:model="periodForm.from" />
                <flux:error name="periodForm.from" />
            </flux:field>

            <flux:field>
                <flux:label>Hasta</flux:label>
                <flux:input type="date" wire:model="periodForm.to" />
                <flux:error name="periodForm.to" />
            </flux:field>

            <flux:field>
                <flux:label>Agrupar por</flux:label>
                <flux:select wire:model="periodForm.groupBy">
                    <option value="day">Día</option>
                    <option value="week">Semana</option>
                    <option value="month">Mes</option>
                </flux:select>
                <flux:error name="periodForm.groupBy" />
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Aplicar</flux:button>
                <flux:button type="button" wire:click="resetPeriod" variant="ghost">Restablecer</flux:button>
            </div>
        </form>

        {{-- Gráfica inline via Alpine.js + JSON data --}}
        <div x-data="analyticsChart({{ $seriesJson }})" x-init="init()" class="mt-4">
            {{-- Selector de métrica --}}
            <div class="mb-3 flex flex-wrap gap-2">
                <template x-for="serie in series" :key="serie.key">
                    <button type="button" class="rounded-full border px-3 py-1 text-sm transition"
                        :class="activeKey === serie.key ?
                            'bg-zinc-800 text-white border-zinc-800 dark:bg-white dark:text-zinc-800 dark:border-white' :
                            'border-zinc-300 text-zinc-600 hover:border-zinc-500 dark:border-zinc-600 dark:text-zinc-300'"
                        @click="activeKey = serie.key" x-text="serie.label"></button>
                </template>
            </div>

            {{-- Stats de la métrica activa --}}
            <template x-if="activeSerie">
                <div class="mb-4 grid grid-cols-3 gap-4 text-center text-sm">
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Total</p>
                        <p class="text-lg font-semibold" x-text="activeSerie.total.toLocaleString()"></p>
                    </div>
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Promedio</p>
                        <p class="text-lg font-semibold" x-text="activeSerie.average.toLocaleString()"></p>
                    </div>
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Pico</p>
                        <p class="text-lg font-semibold" x-text="activeSerie.peak.toLocaleString()"></p>
                    </div>
                </div>
            </template>

            {{-- Gráfica de barras SVG simple --}}
            <template x-if="activeSerie && activeSerie.points.length > 0">
                <div class="overflow-x-auto">
                    <svg :width="chartWidth" height="160" class="min-w-full" aria-label="Gráfica de serie temporal"
                        role="img">
                        <template x-for="(bar, i) in bars" :key="i">
                            <g>
                                <rect :x="bar.x" :y="bar.y" :width="bar.w"
                                    :height="bar.h" class="fill-blue-500 dark:fill-blue-400 opacity-80"
                                    rx="2"></rect>
                                <title x-text="bar.date + ': ' + bar.value"></title>
                            </g>
                        </template>
                    </svg>
                    <div class="mt-1 flex justify-between text-xs text-zinc-400" x-show="bars.length > 0">
                        <span x-text="bars[0]?.date ?? ''"></span>
                        <span x-text="bars[bars.length-1]?.date ?? ''"></span>
                    </div>
                </div>
            </template>

            <template x-if="!activeSerie || activeSerie.points.length === 0">
                <p class="py-8 text-center text-sm text-zinc-500">Sin datos para el periodo seleccionado.</p>
            </template>
        </div>
    </flux:card>
</div>

@push('scripts')
    <script>
        function analyticsChart(seriesData) {
            return {
                series: seriesData,
                activeKey: seriesData.length > 0 ? seriesData[0].key : null,
                get activeSerie() {
                    return this.series.find(s => s.key === this.activeKey) ?? null;
                },
                get chartWidth() {
                    const count = this.activeSerie?.points?.length ?? 0;
                    return Math.max(600, count * 24);
                },
                get bars() {
                    const points = this.activeSerie?.points ?? [];
                    if (points.length === 0) return [];
                    const maxVal = Math.max(...points.map(p => p.value), 1);
                    const chartH = 140;
                    const barW = Math.max(10, Math.floor((this.chartWidth - 20) / points.length) - 2);
                    return points.map((p, i) => ({
                        x: 10 + i * (barW + 2),
                        y: chartH - Math.round((p.value / maxVal) * chartH),
                        w: barW,
                        h: Math.max(2, Math.round((p.value / maxVal) * chartH)),
                        date: p.date,
                        value: p.value,
                    }));
                },
                init() {},
            };
        }
    </script>
@endpush
