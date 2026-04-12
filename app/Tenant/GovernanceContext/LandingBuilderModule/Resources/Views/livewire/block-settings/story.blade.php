<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Nuestra historia" />

    @php $milestones = $editingSettings['milestones'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($milestones as $i => $milestone)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2 md:grid-cols-3">
                    <flux:input wire:model="editingSettings.milestones.{{ $i }}.year" label="Año"
                        placeholder="2026" />
                    <div class="md:col-span-2">
                        <flux:input wire:model="editingSettings.milestones.{{ $i }}.event" label="Hito"
                            placeholder="Lanzamiento de producto" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
