<section class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Landing Builder</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            Editor modular por bloques con previsualización y publicación controlada.
        </p>
    </div>

    @if ($message)
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
            {{ $message }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[280px_1fr_420px]">
        <aside
            class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-2">
                <flux:heading size="sm">Plantillas</flux:heading>
                <p class="text-xs text-zinc-500">Elige una base y luego personalízala bloque por bloque.</p>
            </div>

            <div class="grid gap-2">
                @foreach ($availableTemplates as $template)
                    <button type="button" wire:click="selectTemplate('{{ $template['key'] }}')"
                        class="w-full rounded-lg border p-3 text-left transition {{ $templateKey === $template['key']
                            ? 'border-zinc-900 bg-zinc-50 dark:border-zinc-200 dark:bg-zinc-800'
                            : 'border-zinc-200 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $template['name'] }}</div>
                                <div class="mt-0.5 text-xs text-zinc-500">{{ $template['vibe'] }}</div>
                            </div>
                            <span class="mt-1 inline-block size-4 rounded-full border border-white/40"
                                style="background: {{ $template['primary_color'] }}"></span>
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="h-px bg-zinc-200 dark:bg-zinc-700"></div>

            <div class="space-y-2">
                <flux:heading size="sm">Bloques</flux:heading>
                <p class="text-xs text-zinc-500">Activa, desactiva y edita cada sección de tu landing.</p>
            </div>

            <div class="space-y-2">
                @foreach ($blocks as $block)
                    <button type="button" wire:click="selectBlock({{ $block['id'] }})"
                        class="w-full rounded-lg border px-3 py-2 text-left text-sm transition
                        {{ $selectedBlockId === $block['id']
                            ? 'border-brand-500 bg-brand-50 text-zinc-900 dark:border-brand-400 dark:bg-zinc-800 dark:text-zinc-100'
                            : 'border-zinc-200 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-medium">{{ ucfirst($block['block_type']) }}</span>
                            <flux:badge color="{{ $block['is_active'] ? 'green' : 'zinc' }}" size="sm">
                                {{ $block['is_active'] ? 'Activo' : 'Inactivo' }}
                            </flux:badge>
                        </div>
                    </button>
                @endforeach
            </div>
        </aside>

        <div class="space-y-6">
            <form wire:submit="save"
                class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
                <flux:heading size="sm">Configuración global</flux:heading>
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input wire:model="form.siteName" label="Nombre del sitio" placeholder="Mi Empresa" />
                    <flux:input wire:model="form.primaryColor" type="color" label="Color primario" />
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:select wire:model="form.status" label="Estado">
                        <flux:select.option value="draft">Borrador</flux:select.option>
                        <flux:select.option value="published">Publicado</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="form.cta" label="CTA por defecto" placeholder="Comenzar" />
                </div>

                <div class="flex flex-wrap justify-end gap-2">
                    <flux:button type="button" variant="subtle" wire:click="unpublish">Guardar como borrador
                    </flux:button>
                    <flux:button type="button" variant="primary" wire:click="publish">Publicar</flux:button>
                    <flux:button type="submit" variant="ghost">Guardar global</flux:button>
                </div>
            </form>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">Vista previa</flux:heading>
                    <a class="text-xs underline" target="_blank" href="{{ $previewUrl }}">Abrir preview</a>
                </div>

                <iframe src="{{ $previewUrl }}"
                    class="h-135 w-full rounded-lg border border-zinc-200 dark:border-zinc-700"></iframe>
            </div>
        </div>

        <aside
            class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="sm">Editor de bloque</flux:heading>
                    <p class="text-xs text-zinc-500">Tipo: {{ $selectedBlockType ?: 'N/A' }}</p>
                </div>
                @if ($selectedBlockType)
                    <flux:button type="button" variant="ghost" size="sm" wire:click="toggleBlock">
                        {{ $editingBlockActive ? 'Desactivar' : 'Activar' }}
                    </flux:button>
                @endif
            </div>

            <div class="space-y-4">
                @if ($selectedBlockType)
                    @includeIf('landing-builder::livewire.block-settings.' . $selectedBlockType)
                @else
                    <p class="text-sm text-zinc-500">Selecciona un bloque para editar.</p>
                @endif
            </div>

            <div class="pt-2">
                <flux:button type="button" variant="primary" class="w-full" wire:click="saveBlock">
                    Guardar bloque
                </flux:button>
            </div>
        </aside>
    </div>

    <p class="text-xs text-zinc-500">
        URL pública: <a class="underline" target="_blank" href="{{ $publicUrl }}">{{ $publicUrl }}</a>
    </p>
</section>
