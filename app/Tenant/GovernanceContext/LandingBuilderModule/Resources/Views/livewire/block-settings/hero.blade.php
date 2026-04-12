<div class="space-y-4">
    <flux:input wire:model="editingSettings.headline" label="Headline" placeholder="Bienvenido a nuestro workspace" />
    <flux:textarea wire:model="editingSettings.subheadline" label="Subheadline" rows="3"
        placeholder="Presenta tu propuesta de valor para convertir visitas en registros." />
    <div class="grid gap-3 md:grid-cols-2">
        <flux:input wire:model="editingSettings.cta_text" label="Texto CTA" placeholder="Comenzar" />
        <flux:input wire:model="editingSettings.cta_url" label="URL CTA" placeholder="/register" />
    </div>
</div>
