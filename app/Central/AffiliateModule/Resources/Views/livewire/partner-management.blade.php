<div class="flex flex-col gap-6">
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Partners Afiliados</flux:heading>
            <flux:subheading>Gestiona los socios que refieren nuevos clientes a la plataforma.</flux:subheading>
        </div>
        <flux:modal.trigger name="partner-modal">
            <flux:button variant="primary" icon="plus" color="indigo">Nuevo Partner</flux:button>
        </flux:modal.trigger>
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
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por nombre, email o código..."
                icon="magnifying-glass" class="max-w-md" />
            <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $partners->total() }}</b></flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Partner / Código</flux:table.column>
                <flux:table.column>Contacto</flux:table.column>
                <flux:table.column>Comisión</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($partners as $partner)
                    <flux:table.row :key="$partner->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $partner->name }}</span>
                                <span
                                    class="text-xs font-mono text-orange-600 font-bold uppercase">{{ $partner->code }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $partner->email }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col text-xs">
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $partner->payout_type === 'percentage' ? $partner->payout_value . '%' : '$' . number_format($partner->payout_value / 100, 2) }}
                                </span>
                                <span class="text-[10px] text-zinc-500 uppercase">{{ $partner->payout_type }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $partner->is_active ? 'green' : 'zinc' }}"
                                inset="top">
                                {{ $partner->is_active ? 'ACTIVO' : 'INACTIVO' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="togglePartnerStatus({{ $partner->id }})" variant="ghost"
                                    size="sm" icon="{{ $partner->is_active ? 'pause' : 'play' }}"
                                    tooltip="{{ $partner->is_active ? 'Desactivar' : 'Activar' }}" />

                                <flux:button variant="ghost" size="sm" icon="pencil-square" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500 italic">
                            No se encontraron partners.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $partners->links() }}
        </div>
    </flux:card>

    <flux:modal name="partner-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Nuevo Partner Afiliado</flux:heading>
                <flux:subheading>Registra un nuevo socio para el programa de referidos.</flux:subheading>
            </div>

            <form wire:submit="createPartner" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="form.name" label="Nombre Completo" placeholder="John Doe" required />
                    <flux:input wire:model="form.code" label="Código Único" placeholder="PARTNER2026" required />
                </div>

                <flux:input wire:model="form.email" type="email" label="Email de Contacto"
                    placeholder="partner@example.com" required />

                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model="form.payoutType" label="Tipo de Comisión">
                        <option value="percentage">Porcentaje (%)</option>
                        <option value="fixed">Fijo (USD)</option>
                    </flux:select>
                    <flux:input wire:model="form.payoutValue" type="number" label="Valor (en centavos si es fijo)"
                        required />
                </div>

                <flux:textarea wire:model="form.notes" label="Notas Internas"
                    placeholder="Información adicional del partner..." />

                <div class="flex items-center gap-2">
                    <flux:checkbox wire:model="form.isActive" label="Activar inmediatamente" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Registrar Partner</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
