<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="Contáctanos" />
    <div class="grid gap-3 md:grid-cols-2">
        <flux:input wire:model="editingSettings.email" label="Email" placeholder="hola@empresa.com" />
        <flux:input wire:model="editingSettings.phone" label="Teléfono" placeholder="+52 55 ..." />
    </div>
    <flux:input wire:model="editingSettings.address" label="Dirección" placeholder="Ciudad, País" />
</div>
