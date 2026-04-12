<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Clientes que confían en nosotros" />

    @php $items = $editingSettings['items'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($items as $i => $item)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2">
                    <flux:textarea wire:model="editingSettings.items.{{ $i }}.quote" label="Testimonio"
                        rows="2" placeholder="Comparte la frase del cliente." />
                    <div class="grid gap-2 md:grid-cols-2">
                        <flux:input wire:model="editingSettings.items.{{ $i }}.author" label="Autor"
                            placeholder="María Pérez" />
                        <flux:input wire:model="editingSettings.items.{{ $i }}.role" label="Cargo"
                            placeholder="COO" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
