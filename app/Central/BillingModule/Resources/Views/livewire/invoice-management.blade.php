<div class="flex flex-col gap-6">
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Historial de Facturas</flux:heading>
            <flux:subheading>Registro histórico de cobros generados a los inquilinos.</flux:subheading>
        </div>
    </header>

    <flux:card class="overflow-hidden">
        <div
            class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por número de factura o tenant..."
                icon="magnifying-glass" class="max-w-md" />
            <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $invoices->total() }}</b></flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Factura</flux:table.column>
                <flux:table.column>Tenant / ID</flux:table.column>
                <flux:table.column>Importe</flux:table.column>
                <flux:table.column>Periodo</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column>Fecha Pago</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($invoices as $invoice)
                    <flux:table.row :key="$invoice->id">
                        <flux:table.cell>
                            <span class="font-mono text-sm font-bold">{{ $invoice->invoice_number }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span
                                    class="text-sm font-medium">{{ $invoice->subscription?->tenant?->displayName() ?? '-' }}</span>
                                <span
                                    class="text-[10px] font-mono text-zinc-500 uppercase">{{ $invoice->tenant_id }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">${{ number_format($invoice->amount_cents / 100, 2) }}</span>
                            <span class="text-[10px] text-zinc-500">{{ strtoupper($invoice->currency) }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" variant="outline">{{ strtoupper($invoice->billing_period) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm"
                                color="{{ match ($invoice->status) {
                                    'paid' => 'green',
                                    'open' => 'amber',
                                    'void' => 'red',
                                    default => 'zinc',
                                } }}"
                                inset="top">
                                {{ strtoupper($invoice->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i') : '-' }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="proforma-modal">
                                    <flux:button wire:click="showProforma({{ $invoice->id }})" variant="ghost" size="sm" icon="document-text" tooltip="Ver Proforma">
                                        Proforma
                                    </flux:button>
                                </flux:modal.trigger>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center text-zinc-500 italic">
                            No se encontraron facturas registradas.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $invoices->links() }}
        </div>
    </flux:card>

    {{-- Modal Proforma --}}
    <flux:modal name="proforma-modal" class="md:w-[700px]">
        <div class="space-y-6">
            @if($selectedInvoice)
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div>
                        <flux:heading size="lg">PROFORMA DE FACTURA</flux:heading>
                        <flux:subheading>ID: {{ $selectedInvoice->invoice_number }}</flux:subheading>
                    </div>
                    <div class="text-right">
                        <flux:badge size="sm" color="{{ $selectedInvoice->status === 'paid' ? 'green' : 'amber' }}">
                            {{ strtoupper($selectedInvoice->status) }}
                        </flux:badge>
                        <p class="text-xs text-zinc-500 mt-1">{{ $selectedInvoice->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <flux:heading size="sm" class="mb-2">EMISOR</flux:heading>
                        <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Plinth SaaS Platform</p>
                        <p class="text-xs text-zinc-500">Central Billing System</p>
                    </div>
                    <div class="text-right">
                        <flux:heading size="sm" class="mb-2">RECEPTOR (TENANT)</flux:heading>
                        <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $selectedInvoice->subscription?->tenant?->displayName() }}</p>
                        <p class="text-[10px] font-mono text-zinc-500">{{ $selectedInvoice->tenant_id }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Descripción</flux:table.column>
                            <flux:table.column class="text-right">Total</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">Suscripción: {{ $selectedInvoice->subscription?->plan?->name }}</span>
                                        <span class="text-xs text-zinc-500">Periodo: {{ strtoupper($selectedInvoice->billing_period) }}</span>
                                        @if($selectedInvoice->description)
                                            <span class="text-xs italic text-zinc-400 mt-1">{{ $selectedInvoice->description }}</span>
                                        @endif
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="text-right align-top">
                                    <span class="font-bold">${{ number_format($selectedInvoice->amount_cents / 100, 2) }}</span>
                                </flux:table.cell>
                            </flux:table.row>
                        </flux:table.rows>
                    </flux:table>
                </div>

                <div class="flex flex-col items-end gap-2 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="flex justify-between w-48 text-sm">
                        <span class="text-zinc-500">Subtotal:</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">${{ number_format($selectedInvoice->amount_cents / 100, 2) }}</span>
                    </div>
                    <div class="flex justify-between w-48 text-sm">
                        <span class="text-zinc-500">Impuestos (0%):</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">$0.00</span>
                    </div>
                    <flux:separator class="w-48" />
                    <div class="flex justify-between w-48 text-lg font-bold">
                        <span class="text-zinc-900 dark:text-zinc-100">TOTAL:</span>
                        <span class="text-orange-600">${{ number_format($selectedInvoice->amount_cents / 100, 2) }}</span>
                    </div>
                </div>

                <div class="flex justify-between gap-2 pt-6">
                    <flux:button variant="ghost" icon="printer">Imprimir</flux:button>
                    <flux:modal.close>
                        <flux:button variant="filled">Cerrar</flux:button>
                    </flux:modal.close>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-zinc-500 italic">
                    <flux:icon name="loading" class="animate-spin mb-4" />
                    <p>Cargando información de proforma...</p>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
