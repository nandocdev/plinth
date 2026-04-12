<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Servicios principales" />

    @php $items = $editingSettings['items'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($items as $i => $item)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2">
                    <flux:input wire:model="editingSettings.items.{{ $i }}.title" label="Nombre"
                        placeholder="Implementación" />
                    <flux:textarea wire:model="editingSettings.items.{{ $i }}.description" rows="2"
                        label="Descripción" placeholder="Explica brevemente este servicio." />
                </div>
            </div>
        @endforeach
    </div>
</div>
