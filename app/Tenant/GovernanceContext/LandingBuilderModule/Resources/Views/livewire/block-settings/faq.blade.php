<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Preguntas frecuentes" />

    @php $items = $editingSettings['items'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($items as $i => $item)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2">
                    <flux:input wire:model="editingSettings.items.{{ $i }}.question" label="Pregunta"
                        placeholder="¿Cómo funciona?" />
                    <flux:textarea wire:model="editingSettings.items.{{ $i }}.answer" rows="2"
                        label="Respuesta" placeholder="Explica la respuesta breve." />
                </div>
            </div>
        @endforeach
    </div>
</div>
