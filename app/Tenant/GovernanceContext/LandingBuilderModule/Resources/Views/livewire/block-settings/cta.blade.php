<div class="space-y-4">
    <flux:input wire:model="editingSettings.title" label="Título" placeholder="¿Listo para empezar?" />
    <flux:textarea wire:model="editingSettings.subtitle" rows="2" label="Subtítulo"
        placeholder="Activa tu workspace en minutos." />
    <div class="grid gap-3 md:grid-cols-2">
        <flux:input wire:model="editingSettings.button_text" label="Texto botón" placeholder="Crear cuenta" />
        <flux:input wire:model="editingSettings.button_url" label="URL botón" placeholder="/register" />
    </div>
</div>
