<div class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Billing') }}</flux:heading>
                <flux:subheading>{{ __('Gestiona planes y suscripciones desde el contexto central.') }}
                </flux:subheading>
            </div>

            <flux:button wire:click="syncSubscriptionLifecycle" variant="filled">
                {{ __('Sincronizar lifecycle') }}
            </flux:button>
        </div>

        @if (session('status'))
            <flux:text class="mt-4 text-green-600 dark:text-green-400">{{ session('status') }}</flux:text>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg">
            {{ $editingPlanId ? __('Editar plan') : __('Crear plan') }}
        </flux:heading>

        <form wire:submit="{{ $editingPlanId ? 'updatePlan' : 'createPlan' }}" class="mt-4 grid gap-4 md:grid-cols-3">
            <flux:input wire:model="planForm.name" :label="__('Nombre')" required />
            <flux:input wire:model="planForm.slug" :label="__('Slug')" :placeholder="__('pro-monthly')" required />
            <flux:input wire:model="planForm.priceMonthlyCents" type="number" :label="__('Precio mensual (centavos)')"
                min="1" required />

            <flux:input wire:model="planForm.priceYearlyCents" type="number" :label="__('Precio anual (centavos)')"
                min="1" />
            <flux:input wire:model="planForm.trialDays" type="number" :label="__('Dias de trial')" min="0"
                max="365" required />
            <flux:input wire:model="planForm.sortOrder" type="number" :label="__('Orden')" min="0"
                max="999" required />

            <flux:input wire:model="planForm.maxUsersSoft" type="number" :label="__('Limite soft usuarios')"
                min="1" />
            <flux:input wire:model="planForm.maxUsersHard" type="number" :label="__('Limite hard usuarios')"
                min="1" />
            <flux:input wire:model="planForm.maxStorageMbSoft" type="number" :label="__('Limite soft storage (MB)')"
                min="1" />
            <flux:input wire:model="planForm.maxStorageMbHard" type="number" :label="__('Limite hard storage (MB)')"
                min="1" />

            <flux:input wire:model="planForm.features" :label="__('Features (coma separada)')"
                :placeholder="__('api_access, priority_support')" class="md:col-span-2" />

            <div class="flex items-end gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" wire:model="planForm.isActive"
                        class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800" />
                    {{ __('Activo') }}
                </label>
            </div>

            <div class="md:col-span-3 flex gap-2">
                <flux:button type="submit" variant="primary">
                    {{ $editingPlanId ? __('Guardar cambios') : __('Crear plan') }}
                </flux:button>

                @if ($editingPlanId)
                    <flux:button type="button" wire:click="cancelPlanEditing" variant="filled">
                        {{ __('Cancelar edicion') }}
                    </flux:button>
                @endif
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <flux:input wire:model.live.debounce.400ms="plansSearch" :label="__('Buscar planes')"
                :placeholder="__('Nombre o slug')" class="md:max-w-sm" />
            <flux:text>{{ __('Total: :count', ['count' => $plans->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Nombre') }}</th>
                        <th class="py-3 pr-3">{{ __('Slug') }}</th>
                        <th class="py-3 pr-3">{{ __('Precios') }}</th>
                        <th class="py-3 pr-3">{{ __('Trial') }}</th>
                        <th class="py-3 pr-3">{{ __('Limites') }}</th>
                        <th class="py-3 pr-3">{{ __('Estado') }}</th>
                        <th class="py-3 pr-3">{{ __('Suscripciones') }}</th>
                        <th class="py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="plan-{{ $plan->id }}">
                            <td class="py-3 pr-3">{{ $plan->name }}</td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ $plan->slug }}</td>
                            <td class="py-3 pr-3">
                                <div>{{ __('Mensual: :amount', ['amount' => $plan->price_monthly_cents]) }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Anual: :amount', ['amount' => $plan->price_yearly_cents ?? '-']) }}
                                </div>
                            </td>
                            <td class="py-3 pr-3">{{ $plan->trial_days }} {{ __('dias') }}</td>
                            <td class="py-3 pr-3">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Users: :soft / :hard', ['soft' => $plan->max_users_soft ?? '-', 'hard' => $plan->max_users_hard ?? '-']) }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Storage MB: :soft / :hard', ['soft' => $plan->max_storage_mb_soft ?? '-', 'hard' => $plan->max_storage_mb_hard ?? '-']) }}
                                </div>
                            </td>
                            <td class="py-3 pr-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ $plan->is_active ? __('Activo') : __('Inactivo') }}
                                </span>
                            </td>
                            <td class="py-3 pr-3">{{ $plan->subscriptions_count }}</td>
                            <td class="py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="startPlanEditing({{ $plan->id }})" variant="filled"
                                        size="sm">
                                        {{ __('Editar') }}
                                    </flux:button>
                                    <flux:button wire:click="deletePlan({{ $plan->id }})"
                                        wire:confirm="{{ __('Esto eliminara el plan. Continuar?') }}" variant="danger"
                                        size="sm">
                                        {{ __('Eliminar') }}
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-zinc-500">
                                {{ __('Sin planes registrados.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $plans->links(data: ['paginator' => 'plansPage']) }}
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg">
            {{ $editingSubscriptionId ? __('Editar suscripcion') : __('Crear suscripcion') }}
        </flux:heading>

        <form wire:submit="{{ $editingSubscriptionId ? 'updateSubscription' : 'createSubscription' }}"
            class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label for="subscription-tenant" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Tenant') }}
                </label>
                <select id="subscription-tenant" wire:model="subscriptionForm.tenantId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                    required>
                    <option value="">{{ __('Selecciona tenant') }}</option>
                    @foreach ($tenantOptions as $tenant)
                        <option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</option>
                    @endforeach
                </select>
                @error('subscriptionForm.tenantId')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subscription-plan" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Plan') }}
                </label>
                <select id="subscription-plan" wire:model="subscriptionForm.planId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                    required>
                    <option value="">{{ __('Selecciona plan') }}</option>
                    @foreach ($planOptions as $planOption)
                        <option value="{{ $planOption['id'] }}">{{ $planOption['name'] }}</option>
                    @endforeach
                </select>
                @error('subscriptionForm.planId')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subscription-period" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Periodo') }}
                </label>
                <select id="subscription-period" wire:model="subscriptionForm.billingPeriod"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                    required>
                    <option value="monthly">{{ __('Mensual') }}</option>
                    <option value="yearly">{{ __('Anual') }}</option>
                </select>
            </div>

            <div>
                <label for="subscription-status" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Estado') }}
                </label>
                <select id="subscription-status" wire:model="subscriptionForm.status"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                    required>
                    <option value="trialing">trialing</option>
                    <option value="active">active</option>
                    <option value="past_due">past_due</option>
                    <option value="canceled">canceled</option>
                    <option value="deleted">deleted</option>
                </select>
            </div>

            <flux:input wire:model="subscriptionForm.trialEndsAt" type="datetime-local"
                :label="__('Fin de trial (opcional)')" />
            <flux:input wire:model="subscriptionForm.endsAt" type="datetime-local"
                :label="__('Fin de suscripcion (opcional)')" />

            <div class="md:col-span-3 flex gap-2">
                <flux:button type="submit" variant="primary">
                    {{ $editingSubscriptionId ? __('Guardar cambios') : __('Crear suscripcion') }}
                </flux:button>

                @if ($editingSubscriptionId)
                    <flux:button type="button" wire:click="cancelSubscriptionEditing" variant="filled">
                        {{ __('Cancelar edicion') }}
                    </flux:button>
                @endif
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <flux:input wire:model.live.debounce.400ms="subscriptionsSearch" :label="__('Buscar suscripciones')"
                :placeholder="__('Tenant ID, plan o estado')" class="md:max-w-sm" />
            <flux:text>{{ __('Total: :count', ['count' => $subscriptions->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Tenant') }}</th>
                        <th class="py-3 pr-3">{{ __('Plan') }}</th>
                        <th class="py-3 pr-3">{{ __('Periodo') }}</th>
                        <th class="py-3 pr-3">{{ __('Estado') }}</th>
                        <th class="py-3 pr-3">{{ __('Trial') }}</th>
                        <th class="py-3 pr-3">{{ __('Fin') }}</th>
                        <th class="py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $subscription)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800"
                            wire:key="subscription-{{ $subscription->id }}">
                            <td class="py-3 pr-3">
                                <div class="font-mono text-xs">{{ $subscription->tenant_id }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $subscription->tenant?->displayName() ?? '-' }}</div>
                            </td>
                            <td class="py-3 pr-3">{{ $subscription->plan?->name ?? '-' }}</td>
                            <td class="py-3 pr-3">{{ $subscription->billing_period }}</td>
                            <td class="py-3 pr-3">{{ $subscription->status }}</td>
                            <td class="py-3 pr-3">
                                {{ $subscription->trial_ends_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="py-3 pr-3">{{ $subscription->ends_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="startSubscriptionEditing({{ $subscription->id }})"
                                        variant="filled" size="sm">
                                        {{ __('Editar') }}
                                    </flux:button>

                                    <flux:button wire:click="deleteSubscription({{ $subscription->id }})"
                                        wire:confirm="{{ __('Esto eliminara la suscripcion. Continuar?') }}"
                                        variant="danger" size="sm">
                                        {{ __('Eliminar') }}
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-zinc-500">
                                {{ __('Sin suscripciones registradas.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $subscriptions->links(data: ['paginator' => 'subscriptionsPage']) }}
        </div>
    </div>
</div>
</div>
