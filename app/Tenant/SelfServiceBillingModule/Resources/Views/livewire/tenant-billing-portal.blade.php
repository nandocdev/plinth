<div>
    {{-- Encabezado del portal --}}
    <div class="mb-6">
        <flux:heading size="xl">Portal de Facturación</flux:heading>
        <flux:subheading>Gestiona tu suscripción y consulta tus facturas</flux:subheading>
    </div>

    {{-- Tabs de navegación --}}
    <flux:tab.group>
        <flux:tabs>
            <flux:tab name="overview" wire:click="$set('activeTab', 'overview')" :current="$activeTab === 'overview'">
                <flux:icon name="home" class="size-4 mr-1" />
                Plan actual
            </flux:tab>
            <flux:tab name="upgrade" wire:click="$set('activeTab', 'upgrade')" :current="$activeTab === 'upgrade'">
                <flux:icon name="arrow-trending-up" class="size-4 mr-1" />
                Cambiar plan
            </flux:tab>
            <flux:tab name="invoices" wire:click="$set('activeTab', 'invoices')" :current="$activeTab === 'invoices'">
                <flux:icon name="document-text" class="size-4 mr-1" />
                Facturas
            </flux:tab>
        </flux:tabs>

        {{-- Tab: Resumen de suscripción actual --}}
        <flux:tab.panel name="overview" :active="$activeTab === 'overview'">
            <div class="mt-6">
                @if ($upgradeSuccess)
                    <flux:callout variant="success" icon="check-circle" class="mb-6">
                        {{ $upgradeSuccess }}
                    </flux:callout>
                @endif

                @if ($overview->hasSubscription())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:card class="p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">
                                        Plan</p>
                                    <p class="text-xl font-bold text-zinc-900 dark:text-white">
                                        {{ $overview->planName ?? '—' }}</p>
                                    <p class="text-sm text-zinc-500 mt-1">
                                        {{ $overview->billingPeriod === 'yearly' ? 'Facturación anual' : 'Facturación mensual' }}
                                    </p>
                                </div>
                                <flux:badge
                                    :color="match($overview->status) {
                                                                            'active' => 'green',
                                                                            'trialing' => 'blue',
                                                                            'past_due' => 'yellow',
                                                                            'canceled' => 'red',
                                                                            default => 'zinc',
                                                                        }">
                                    {{ match ($overview->status) {
                                        'active' => 'Activo',
                                        'trialing' => 'Prueba',
                                        'past_due' => 'Pago pendiente',
                                        'canceled' => 'Cancelado',
                                        'deleted' => 'Eliminado',
                                        default => $overview->status,
                                    } }}
                                </flux:badge>
                            </div>
                        </flux:card>

                        <flux:card class="p-5">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">Precio
                                actual</p>
                            <p class="text-xl font-bold text-zinc-900 dark:text-white">
                                @if ($overview->priceSnapshotCents !== null)
                                    ${{ number_format($overview->priceSnapshotCents / 100, 2) }} USD
                                    <span class="text-sm font-normal text-zinc-500">/
                                        {{ $overview->billingPeriod === 'yearly' ? 'año' : 'mes' }}</span>
                                @else
                                    —
                                @endif
                            </p>
                        </flux:card>

                        @if ($overview->startsAt)
                            <flux:card class="p-5">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">Inicio
                                    de suscripción</p>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($overview->startsAt)->format('d M Y') }}
                                </p>
                            </flux:card>
                        @endif

                        @if ($overview->trialEndsAt)
                            <flux:card class="p-5 border-blue-300 dark:border-blue-700">
                                <p class="text-xs text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Período
                                    de prueba hasta</p>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($overview->trialEndsAt)->format('d M Y') }}
                                </p>
                            </flux:card>
                        @endif

                        @if ($overview->endsAt && in_array($overview->status, ['canceled']))
                            <flux:card class="p-5 border-red-300 dark:border-red-700">
                                <p class="text-xs text-red-600 dark:text-red-400 uppercase tracking-wide mb-1">Acceso
                                    hasta</p>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($overview->endsAt)->format('d M Y') }}
                                </p>
                            </flux:card>
                        @endif
                    </div>

                    @if ($overview->isActive())
                        <div class="mt-4">
                            <flux:button wire:click="$set('activeTab', 'upgrade')" variant="outline"
                                icon="arrow-trending-up">
                                Cambiar de plan
                            </flux:button>
                        </div>
                    @endif
                @else
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        No tienes una suscripción activa. Contacta con soporte para activar tu cuenta.
                    </flux:callout>
                @endif
            </div>
        </flux:tab.panel>

        {{-- Tab: Cambio de plan --}}
        <flux:tab.panel name="upgrade" :active="$activeTab === 'upgrade'">
            <div class="mt-6">
                @if (!$overview->hasSubscription())
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        No tienes una suscripción activa. No puedes cambiar de plan.
                    </flux:callout>
                @else
                    @if ($upgradeError)
                        <flux:callout variant="danger" icon="x-circle" class="mb-6">{{ $upgradeError }}</flux:callout>
                    @endif

                    <form wire:submit="requestUpgrade" class="space-y-6">
                        {{-- Selección de período --}}
                        <div>
                            <flux:label class="mb-3">Período de facturación</flux:label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <flux:radio wire:model="upgradeForm.billingPeriod" value="monthly" />
                                    <span class="text-sm">Mensual</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <flux:radio wire:model="upgradeForm.billingPeriod" value="yearly" />
                                    <span class="text-sm">Anual <flux:badge color="green" size="sm">Ahorra ~17%
                                        </flux:badge></span>
                                </label>
                            </div>
                            <flux:error name="upgradeForm.billingPeriod" />
                        </div>

                        {{-- Grid de planes --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach ($plans as $plan)
                                @php
                                    $price =
                                        $upgradeForm->billingPeriod === 'yearly' && $plan->price_yearly_cents
                                            ? $plan->price_yearly_cents
                                            : $plan->price_monthly_cents;
                                    $isCurrent = $plan->id === $overview->currentPlanId;
                                @endphp
                                <label
                                    class="relative flex flex-col cursor-pointer rounded-xl border-2 p-5 transition
                                        {{ $upgradeForm->planId == $plan->id
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/30'
                                            : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}"
                                    wire:click="$set('upgradeForm.planId', {{ $plan->id }})">
                                    <input type="radio" name="planId" value="{{ $plan->id }}" class="sr-only"
                                        wire:model="upgradeForm.planId" />

                                    @if ($isCurrent)
                                        <flux:badge color="zinc" size="sm" class="absolute top-3 right-3">Actual
                                        </flux:badge>
                                    @endif

                                    <p class="font-semibold text-zinc-900 dark:text-white text-base mb-1">
                                        {{ $plan->name }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        ${{ number_format($price / 100, 2) }}
                                        <span class="text-sm font-normal text-zinc-500">/
                                            {{ $upgradeForm->billingPeriod === 'yearly' ? 'año' : 'mes' }}</span>
                                    </p>

                                    @if (is_array($plan->features) && count($plan->features) > 0)
                                        <ul class="mt-3 space-y-1">
                                            @foreach (array_slice($plan->features, 0, 4) as $feature)
                                                <li
                                                    class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                                    <flux:icon name="check"
                                                        class="size-3.5 text-green-500 flex-shrink-0" />
                                                    {{ $feature }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="upgradeForm.planId" />

                        <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                            <span wire:loading.remove>Confirmar cambio de plan</span>
                            <span wire:loading>Procesando...</span>
                        </flux:button>
                    </form>
                @endif
            </div>
        </flux:tab.panel>

        {{-- Tab: Facturas --}}
        <flux:tab.panel name="invoices" :active="$activeTab === 'invoices'">
            <div class="mt-6">
                @if ($invoices->isEmpty())
                    <flux:callout variant="info" icon="information-circle">
                        Aún no hay facturas disponibles. Se generarán automáticamente cuando tu suscripción esté activa.
                    </flux:callout>
                @else
                    <flux:table>
                        <flux:columns>
                            <flux:column>Fecha</flux:column>
                            <flux:column>Nº Factura</flux:column>
                            <flux:column>Descripción</flux:column>
                            <flux:column>Período</flux:column>
                            <flux:column>Importe</flux:column>
                            <flux:column>Estado</flux:column>
                        </flux:columns>

                        <flux:rows>
                            @foreach ($invoices as $invoice)
                                <flux:row>
                                    <flux:cell class="text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $invoice->created_at->format('d M Y') }}
                                    </flux:cell>
                                    <flux:cell class="font-mono text-xs text-zinc-500">
                                        {{ $invoice->invoice_number }}
                                    </flux:cell>
                                    <flux:cell class="text-sm text-zinc-700 dark:text-zinc-300 max-w-xs truncate">
                                        {{ $invoice->description ?? '—' }}
                                    </flux:cell>
                                    <flux:cell class="text-sm capitalize">
                                        {{ $invoice->billing_period ?? '—' }}
                                    </flux:cell>
                                    <flux:cell class="text-sm font-medium text-zinc-900 dark:text-white">
                                        ${{ number_format($invoice->amount_cents / 100, 2) }} {{ $invoice->currency }}
                                    </flux:cell>
                                    <flux:cell>
                                        <flux:badge
                                            :color="match($invoice->status) {
                                                                                            'paid' => 'green',
                                                                                            'open' => 'yellow',
                                                                                            'void' => 'zinc',
                                                                                            default => 'zinc',
                                                                                        }"
                                            size="sm">
                                            {{ match ($invoice->status) {
                                                'paid' => 'Pagada',
                                                'open' => 'Pendiente',
                                                'void' => 'Anulada',
                                                default => $invoice->status,
                                            } }}
                                        </flux:badge>
                                    </flux:cell>
                                </flux:row>
                            @endforeach
                        </flux:rows>
                    </flux:table>

                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </flux:tab.panel>
    </flux:tab.group>
</div>
