<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Logros" />

    @php $items = $editingSettings['items'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($items as $i => $item)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2 md:grid-cols-2">
                    <flux:input wire:model="editingSettings.items.{{ $i }}.title" label="Etiqueta"
                        placeholder="Clientes activos" />
                    <flux:input wire:model="editingSettings.items.{{ $i }}.value" label="Valor"
                        placeholder="+10,000" />
                </div>
            </div>
        @endforeach
    </div>
</div>
