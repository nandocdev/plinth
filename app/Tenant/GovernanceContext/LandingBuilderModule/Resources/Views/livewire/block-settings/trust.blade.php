<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Marcas que confían" />

    @php $items = $editingSettings['items'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($items as $i => $item)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:input wire:model="editingSettings.items.{{ $i }}.title" label="Nombre"
                    placeholder="Empresa / Marca" />
            </div>
        @endforeach
    </div>
</div>
