<div class="space-y-4">
    <div class="grid gap-3 md:grid-cols-2">
        <flux:input wire:model="editingSettings.title" label="Título" placeholder="Planes" />
        <flux:input wire:model="editingSettings.currency" label="Moneda" placeholder="$" />
    </div>

    @php $plans = $editingSettings['plans'] ?? []; @endphp

    <div class="space-y-3">
        @foreach ($plans as $i => $plan)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="grid gap-2">
                    <div class="grid gap-2 md:grid-cols-2">
                        <flux:input wire:model="editingSettings.plans.{{ $i }}.name" label="Plan"
                            placeholder="Pro" />
                        <flux:input wire:model="editingSettings.plans.{{ $i }}.price" label="Precio"
                            placeholder="79" />
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <flux:input wire:model="editingSettings.plans.{{ $i }}.period" label="Periodo"
                            placeholder="mes" />
                        <flux:input wire:model="editingSettings.plans.{{ $i }}.cta" label="CTA"
                            placeholder="Elegir plan" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
