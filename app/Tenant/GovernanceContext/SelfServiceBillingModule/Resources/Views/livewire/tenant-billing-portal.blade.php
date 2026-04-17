<div>
    @php
        $tenantCurrency = (string) config('tenant.preferences.currency', 'USD');
    @endphp

    {{-- Encabezado del portal --}}
    <div class="mb-6">
        <flux:heading size="xl">Portal de Facturación</flux:heading>
        <flux:subheading>Gestiona tu suscripción y consulta tus facturas</flux:subheading>
    </div>

    {{-- Navegación por pestañas (compatibilidad sin flux:tab.group) --}}
    <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-3 dark:border-zinc-700">
        <flux:button type="button" size="sm" wire:click="$set('activeTab', 'overview')"
            :variant="$activeTab === 'overview' ? 'primary' : 'ghost'" icon="home">
            Plan actual
        </flux:button>
        <flux:button type="button" size="sm" wire:click="$set('activeTab', 'upgrade')"
            :variant="$activeTab === 'upgrade' ? 'primary' : 'ghost'" icon="arrow-trending-up">
            Cambiar plan
        </flux:button>
        <flux:button type="button" size="sm" wire:click="$set('activeTab', 'invoices')"
            :variant="$activeTab === 'invoices' ? 'primary' : 'ghost'" icon="document-text">
            Facturas
        </flux:button>
    </div>

    {{-- Tab: Resumen de suscripción actual --}}
    @if ($activeTab === 'overview')
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
                                {{ $tenantCurrency }} {{ number_format($overview->priceSnapshotCents / 100, 2) }}
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
                                {{ \Carbon\Carbon::parse($overview->startsAt)->translatedFormat('d M Y') }}
                            </p>
                        </flux:card>
                    @endif

                    @if ($overview->trialEndsAt)
                        <flux:card class="p-5 border-blue-300 dark:border-blue-700">
                            <p class="text-xs text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Período
                                de prueba hasta</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($overview->trialEndsAt)->translatedFormat('d M Y') }}
                            </p>
                        </flux:card>
                    @endif

                    @if ($overview->endsAt && in_array($overview->status, ['canceled']))
                        <flux:card class="p-5 border-red-300 dark:border-red-700">
                            <p class="text-xs text-red-600 dark:text-red-400 uppercase tracking-wide mb-1">Acceso
                                hasta</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($overview->endsAt)->translatedFormat('d M Y') }}
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
    @endif

    {{-- Tab: Cambio de plan --}}
    @if ($activeTab === 'upgrade')
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
                                <flux:radio wire:model.live="upgradeForm.billingPeriod" value="monthly" />
                                <span class="text-sm">Mensual</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <flux:radio wire:model.live="upgradeForm.billingPeriod" value="yearly" />
                                <span class="text-sm">Anual <flux:badge color="green" size="sm">Ahorra ~17%
                                    </flux:badge></span>
                            </label>
                        </div>
                        <flux:error name="upgradeForm.billingPeriod" />
                    </div>

                    {{-- Métodos de pago disponibles por contexto --}}
                    <div>
                        <flux:label class="mb-3">Método de pago disponible para tu contexto</flux:label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($checkoutMethods as $method)
                                @php
                                    $isSelectedMethod = $upgradeForm->methodType === $method['method_type'];
                                @endphp

                                <label wire:key="method-{{ $method['method_type'] }}"
                                    wire:click="$set('upgradeForm.methodType', '{{ $method['method_type'] }}')"
                                    class="relative flex cursor-pointer flex-col rounded-xl border-2 p-4 transition outline-none
                                        {{ $isSelectedMethod
                                            ? 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500 dark:bg-blue-950/30'
                                            : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                                    <input type="radio" class="sr-only" name="methodType"
                                        value="{{ $method['method_type'] }}"
                                        wire:model.change="upgradeForm.methodType" />

                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            @if ($isSelectedMethod)
                                                <flux:icon name="check-circle" variant="solid"
                                                    class="size-4 text-blue-600 dark:text-blue-400" />
                                            @else
                                                <div
                                                    class="size-4 rounded-full border border-zinc-300 dark:border-zinc-600">
                                                </div>
                                            @endif
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                {{ $method['label'] }}</p>
                                        </div>
                                        <flux:badge color="zinc" size="sm">{{ strtoupper($method['provider']) }}
                                        </flux:badge>
                                    </div>

                                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $method['description'] }}</p>

                                    @if ($method['manual_confirmation_required'])
                                        <p class="mt-2 text-xs text-amber-700 dark:text-amber-300">
                                            Puede requerir confirmación manual.
                                        </p>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        <flux:error name="upgradeForm.methodType" />

                        @php
                            $selectedMethod = collect($checkoutMethods)->firstWhere(
                                'method_type',
                                $upgradeForm->methodType,
                            );
                        @endphp

                        @if (is_array($selectedMethod) && (bool) ($selectedMethod['manual_confirmation_required'] ?? false))
                            <flux:callout variant="warning" icon="clock" class="mt-3">
                                {{ $selectedMethod['status_message'] ?? 'Tu pago puede quedar pendiente hasta validación del proveedor.' }}
                            </flux:callout>
                        @endif
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
                                $isSelected = (int) $upgradeForm->planId === (int) $plan->id;
                            @endphp
                            <label wire:key="plan-{{ $plan->id }}"
                                wire:click="$set('upgradeForm.planId', {{ $plan->id }})"
                                class="relative flex flex-col cursor-pointer rounded-xl border-2 p-5 transition outline-none
                                        {{ $isSelected
                                            ? 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500 dark:bg-blue-950/30'
                                            : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                                <input type="radio" name="planId" value="{{ $plan->id }}" class="sr-only"
                                    wire:model.change="upgradeForm.planId" />

                                <div class="flex items-start justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        @if ($isSelected)
                                            <flux:icon name="check-circle" variant="solid"
                                                class="size-5 text-blue-600 dark:text-blue-400" />
                                        @else
                                            <div
                                                class="size-5 rounded-full border border-zinc-300 dark:border-zinc-600">
                                            </div>
                                        @endif
                                        <p class="font-semibold text-zinc-900 dark:text-white text-base">
                                            {{ $plan->name }}</p>
                                    </div>

                                    @if ($isCurrent)
                                        <flux:badge color="zinc" size="sm">Actual
                                        </flux:badge>
                                    @endif
                                </div>
                                <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ $tenantCurrency }} {{ number_format($price / 100, 2) }}
                                    <span class="text-sm font-normal text-zinc-500">/
                                        {{ $upgradeForm->billingPeriod === 'yearly' ? 'año' : 'mes' }}</span>
                                </p>

                                @if (is_array($plan->features) && count($plan->features) > 0)
                                    <ul class="mt-3 space-y-1">
                                        @foreach (array_slice($plan->features, 0, 4) as $feature)
                                            <li
                                                class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                                <flux:icon name="check" class="size-3.5 shrink-0 text-green-500" />
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
    @endif

    {{-- Tab: Facturas --}}
    @if ($activeTab === 'invoices')
        <div class="mt-6">
            @if ($invoices->isEmpty())
                <flux:callout variant="info" icon="information-circle">
                    Aún no hay facturas disponibles. Se generarán automáticamente cuando tu suscripción esté activa.
                </flux:callout>
            @else
                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Nº Factura
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">
                                    Descripción
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Período
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Importe
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                            @foreach ($invoices as $invoice)
                                <tr wire:key="invoice-{{ $invoice->id }}">
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                        {{ $invoice->created_at->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-zinc-500">
                                        {{ $invoice->invoice_number }}
                                    </td>
                                    <td class="max-w-xs truncate px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                        {{ $invoice->description ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 capitalize text-zinc-700 dark:text-zinc-300">
                                        {{ $invoice->billing_period ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                                        ${{ number_format($invoice->amount_cents / 100, 2) }} {{ $invoice->currency }}
                                    </td>
                                    <td class="px-4 py-3">
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
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
