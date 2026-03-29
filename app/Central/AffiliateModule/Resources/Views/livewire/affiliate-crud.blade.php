<div class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="xl">{{ __('Affiliate & referrals') }}</flux:heading>
        <flux:subheading>{{ __('Gestiona afiliados y monitorea conversiones de tenants referidos.') }}</flux:subheading>

        @if (session('status'))
            <flux:text class="mt-4 text-green-600 dark:text-green-400">{{ session('status') }}</flux:text>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg">{{ __('Registrar afiliado') }}</flux:heading>

        <form wire:submit="createPartner" class="mt-4 grid gap-4 md:grid-cols-3">
            <flux:input wire:model="form.code" :label="__('Codigo')" :placeholder="__('PARTNER10')" required />
            <flux:input wire:model="form.name" :label="__('Nombre')" :placeholder="__('Acme Partners')" required />
            <flux:input wire:model="form.email" :label="__('Email')" :placeholder="__('partners@acme.com')"
                required />

            <div>
                <label for="affiliate-payout-type" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Tipo de payout') }}
                </label>
                <select id="affiliate-payout-type" wire:model="form.payoutType"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                    required>
                    <option value="percentage">{{ __('Porcentaje') }}</option>
                    <option value="fixed">{{ __('Monto fijo') }}</option>
                </select>
            </div>

            <flux:input wire:model="form.payoutValue" type="number" step="0.01" min="0"
                :label="__('Valor de payout')" :placeholder="__('10')" required />

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" wire:model="form.isActive"
                        class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800" />
                    {{ __('Activo') }}
                </label>
            </div>

            <flux:textarea wire:model="form.notes" class="md:col-span-3" :label="__('Notas (opcional)')" />

            <div class="md:col-span-3">
                <flux:button type="submit" variant="primary">
                    {{ __('Crear afiliado') }}
                </flux:button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <flux:input wire:model.live.debounce.400ms="partnersSearch" :label="__('Buscar afiliados')"
                :placeholder="__('Codigo, nombre o email')" class="md:max-w-sm" />
            <flux:text>{{ __('Total: :count', ['count' => $partners->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Codigo') }}</th>
                        <th class="py-3 pr-3">{{ __('Afiliado') }}</th>
                        <th class="py-3 pr-3">{{ __('Payout') }}</th>
                        <th class="py-3 pr-3">{{ __('Conversiones') }}</th>
                        <th class="py-3 pr-3">{{ __('Estado') }}</th>
                        <th class="py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partners as $partner)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800"
                            wire:key="partner-{{ $partner->id }}">
                            <td class="py-3 pr-3 font-mono">{{ $partner->code }}</td>
                            <td class="py-3 pr-3">
                                <div>{{ $partner->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $partner->email }}</div>
                            </td>
                            <td class="py-3 pr-3">
                                {{ $partner->payout_type === 'percentage' ? __(':value%', ['value' => $partner->payout_value]) : __('$ :value', ['value' => $partner->payout_value]) }}
                            </td>
                            <td class="py-3 pr-3">{{ $partner->conversions_count }}</td>
                            <td class="py-3 pr-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs {{ $partner->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ $partner->is_active ? __('Activo') : __('Inactivo') }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <flux:button size="sm" variant="filled"
                                    wire:click="togglePartnerStatus({{ $partner->id }})">
                                    {{ $partner->is_active ? __('Desactivar') : __('Activar') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-zinc-500">
                                {{ __('Sin afiliados registrados.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $partners->links(data: ['paginator' => 'partnersPage']) }}
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <flux:input wire:model.live.debounce.400ms="conversionsSearch" :label="__('Buscar conversiones')"
                :placeholder="__('Tenant ID, email o estado')" class="md:max-w-sm" />
            <flux:text>{{ __('Total: :count', ['count' => $conversions->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Tenant') }}</th>
                        <th class="py-3 pr-3">{{ __('Afiliado') }}</th>
                        <th class="py-3 pr-3">{{ __('Email referido') }}</th>
                        <th class="py-3 pr-3">{{ __('Estado') }}</th>
                        <th class="py-3 pr-3">{{ __('Fecha conversion') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversions as $conversion)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800"
                            wire:key="conversion-{{ $conversion->id }}">
                            <td class="py-3 pr-3 font-mono text-xs">{{ $conversion->tenant_id }}</td>
                            <td class="py-3 pr-3">{{ $conversion->partner?->name ?? '-' }}</td>
                            <td class="py-3 pr-3">{{ $conversion->referred_email ?? '-' }}</td>
                            <td class="py-3 pr-3">{{ $conversion->status }}</td>
                            <td class="py-3 pr-3">{{ $conversion->converted_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-zinc-500">
                                {{ __('Sin conversiones registradas.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $conversions->links(data: ['paginator' => 'conversionsPage']) }}
        </div>
    </div>
</div>
