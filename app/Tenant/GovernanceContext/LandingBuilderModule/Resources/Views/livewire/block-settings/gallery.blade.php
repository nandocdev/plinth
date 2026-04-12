<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Galería" />

    @php $images = $editingSettings['images'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($images as $i => $image)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2">
                    <flux:input wire:model="editingSettings.images.{{ $i }}.url" label="URL imagen"
                        placeholder="https://..." />
                    <flux:input wire:model="editingSettings.images.{{ $i }}.alt" label="Texto alternativo"
                        placeholder="Descripción de imagen" />
                </div>
            </div>
        @endforeach
    </div>
</div>
