<div class="flex flex-col gap-6">
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Conversiones de Referidos</flux:heading>
            <flux:subheading>Historial de tenants creados a través de códigos de partners.</flux:subheading>
        </div>
    </header>

    <flux:card class="overflow-hidden">
        <div
            class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por código de partner o tenant..."
                icon="magnifying-glass" class="max-w-md" />
            <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $conversions->total() }}</b></flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Partner</flux:table.column>
                <flux:table.column>Tenant Referido</flux:table.column>
                <flux:table.column>Comisión Generada</flux:table.column>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($conversions as $conversion)
                    <flux:table.row :key="$conversion->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span
                                    class="font-bold text-zinc-900 dark:text-zinc-100">{{ $conversion->partner?->name ?? 'Partner Eliminado' }}</span>
                                <span
                                    class="text-xs font-mono text-orange-600 font-bold">{{ $conversion->referral_code }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col text-sm">
                                <span
                                    class="text-zinc-900 dark:text-zinc-100">{{ $conversion->tenant?->displayName() ?? '-' }}</span>
                                <span
                                    class="text-[10px] font-mono text-zinc-500 uppercase">{{ $conversion->tenant_id }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col text-xs">
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($conversion->commission_amount_cents / 100, 2) }}
                                </span>
                                <span class="text-[10px] text-zinc-500 uppercase">Snapshot al momento</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $conversion->created_at->format('Y-m-d H:i') }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end">
                                <flux:button variant="ghost" size="sm" icon="chevron-right" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500 italic">
                            No se han registrado conversiones aún.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $conversions->links() }}
        </div>
    </flux:card>
</div>
