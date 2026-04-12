<div class="space-y-4">
    <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
        <flux:heading size="xs" class="mb-3">Branding</flux:heading>
        <div class="space-y-3">
            <flux:input wire:model="editingSettings.brand_label" label="Nombre" placeholder="Mi Empresa" />
            <flux:input wire:model="editingSettings.logo_url" type="url" label="URL del logo"
                placeholder="https://..." />
            <div class="grid gap-3 md:grid-cols-2">
                <flux:input wire:model="editingSettings.navbar_bg_color" type="color" label="Color de fondo" />
                <flux:input wire:model="editingSettings.navbar_text_color" type="color" label="Color de texto" />
            </div>
        </div>
    </div>

    <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
        <flux:heading size="xs" class="mb-3">Menú de navegación</flux:heading>
        <div class="space-y-3">
            <flux:input wire:model="editingSettings.navbar_link_color" type="color" label="Color de enlaces" />
            <flux:select wire:model="editingSettings.layout_style" label="Estilo de layout">
                <flux:select.option value="compact">Compacto</flux:select.option>
                <flux:select.option value="normal">Normal</flux:select.option>
                <flux:select.option value="wide">Amplio</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div>
        <flux:heading size="xs" class="mb-2">Info</flux:heading>
        <p class="text-xs text-zinc-500">
            El menú se genera automáticamente con las secciones activas de tu landing.
        </p>
    </div>
</div>
