<div class="flex flex-col gap-6">
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Gestión de Suscripciones</flux:heading>
            <flux:subheading>Monitorea y administra el estado de facturación de cada tenant.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="syncSubscriptionLifecycle" variant="ghost" icon="arrow-path">Sincronizar Lifecycle
            </flux:button>
            <flux:modal.trigger name="subscription-modal">
                <flux:button variant="primary" icon="plus" color="orange">Nueva Suscripción</flux:button>
            </flux:modal.trigger>
        </div>
    </header>

    @if (session('status'))
        <flux:card class="bg-green-50 dark:bg-green-950/20 border-green-200 dark:border-green-900/50 py-3 px-4">
            <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                <flux:icon name="check-circle" variant="micro" />
                <p class="text-sm font-medium">{{ session('status') }}</p>
            </div>
        </flux:card>
    @endif

    <flux:card class="overflow-hidden">
        <div
            class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por Tenant ID, plan o estado..."
                icon="magnifying-glass" class="max-w-md" />
            <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $subscriptions->total() }}</b></flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Tenant</flux:table.column>
                <flux:table.column>Plan & Periodo</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column>Trial Ends</flux:table.column>
                <flux:table.column>Period Ends</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($subscriptions as $subscription)
                    <flux:table.row :key="$subscription->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span
                                    class="font-bold text-zinc-900 dark:text-zinc-100">{{ $subscription->tenant?->displayName() ?? '-' }}</span>
                                <span class="text-xs font-mono text-zinc-500">{{ $subscription->tenant_id }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-1">
                                <flux:badge size="sm" variant="subtle" color="blue">
                                    {{ $subscription->plan?->name ?? 'N/A' }}</flux:badge>
                                <span
                                    class="text-[10px] text-zinc-500 uppercase">{{ $subscription->billing_period }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm"
                                color="{{ match ($subscription->status) {
                                    'active' => 'green',
                                    'trialing' => 'blue',
                                    'past_due' => 'red',
                                    'canceled' => 'zinc',
                                    default => 'zinc',
                                } }}"
                                inset="top">
                                {{ strtoupper($subscription->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d') : '-' }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $subscription->ends_at ? $subscription->ends_at->format('Y-m-d') : '∞' }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="subscription-modal">
                                    <flux:button wire:click="startSubscriptionEditing({{ $subscription->id }})"
                                        variant="ghost" size="sm" icon="pencil-square" />
                                </flux:modal.trigger>
                                <flux:button wire:click="deleteSubscription({{ $subscription->id }})" variant="ghost"
                                    size="sm" icon="trash" color="red"
                                    wire:confirm="¿Eliminar esta suscripción?" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-12 text-center text-zinc-500 italic">
                            No se encontraron suscripciones.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $subscriptions->links() }}
        </div>
    </flux:card>

    <flux:modal name="subscription-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingSubscriptionId ? 'Editar Suscripción' : 'Nueva Suscripción' }}
                </flux:heading>
                <flux:subheading>Asigna un plan y define el estado de facturación para un tenant.</flux:subheading>
            </div>

            <form wire:submit="{{ $editingSubscriptionId ? 'updateSubscription' : 'createSubscription' }}"
                class="space-y-4">
                <flux:select wire:model="subscriptionForm.tenantId" label="Seleccionar Tenant" required>
                    <option value="">-- Elegir Cliente --</option>
                    @foreach ($tenantOptions as $tenant)
                        <option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model="subscriptionForm.planId" label="Plan" required>
                        <option value="">-- Elegir Plan --</option>
                        @foreach ($planOptions as $planOption)
                            <option value="{{ $planOption['id'] }}">{{ $planOption['name'] }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="subscriptionForm.billingPeriod" label="Ciclo de Cobro" required>
                        <option value="monthly">Mensual</option>
                        <option value="yearly">Anual</option>
                    </flux:select>
                </div>

                <flux:select wire:model="subscriptionForm.status" label="Estado Inicial" required>
                    <option value="trialing">Trialing</option>
                    <option value="active">Active</option>
                    <option value="past_due">Past Due</option>
                    <option value="canceled">Canceled</option>
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="subscriptionForm.trialEndsAt" type="datetime-local"
                        label="Fin de Trial (opcional)" />
                    <flux:input wire:model="subscriptionForm.endsAt" type="datetime-local"
                        label="Fin de Suscripción (opcional)" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">
                        {{ $editingSubscriptionId ? 'Guardar Cambios' : 'Asignar Suscripción' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
