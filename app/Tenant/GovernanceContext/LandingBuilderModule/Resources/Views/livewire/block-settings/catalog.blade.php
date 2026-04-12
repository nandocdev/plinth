<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Catálogo" />

    @php $items = $editingSettings['items'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($items as $i => $item)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2">
                    <div class="grid gap-2 md:grid-cols-2">
                        <flux:input wire:model="editingSettings.items.{{ $i }}.name" label="Nombre"
                            placeholder="Producto" />
                        <flux:input wire:model="editingSettings.items.{{ $i }}.price" label="Precio"
                            placeholder="$99" />
                    </div>
                    <flux:textarea wire:model="editingSettings.items.{{ $i }}.description" rows="2"
                        label="Descripción" placeholder="Detalle breve del item" />
                </div>
            </div>
        @endforeach
    </div>
</div>
