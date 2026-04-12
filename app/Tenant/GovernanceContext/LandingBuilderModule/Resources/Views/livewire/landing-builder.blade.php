<section class="space-y-6">
    <div
        class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Landing Builder</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Editor modular por bloques con previsualización y publicación controlada.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 lg:justify-end">
            <flux:button type="button" variant="subtle" wire:click="unpublish">Guardar como borrador
            </flux:button>
            <flux:button type="button" variant="primary" wire:click="publish">Publicar</flux:button>
            <flux:button type="submit" variant="filled" form="landing-global-form">Guardar global</flux:button>
        </div>
    </div>

    @if ($message)
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
            {{ $message }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[280px_1fr_420px]">
        <aside x-data="{ showTemplateModal: false, showAddBlockModal: false }"
            class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @php
                $selectedTemplate = collect($availableTemplates)->firstWhere('key', $templateKey);
            @endphp

            <div class="space-y-2">
                <flux:heading size="sm">Plantillas</flux:heading>
            </div>

            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-zinc-500">Plantilla seleccionada</p>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $selectedTemplate['name'] ?? ucfirst($templateKey) }}</div>
                        <div class="mt-0.5 text-xs text-zinc-500">{{ $selectedTemplate['vibe'] ?? '' }}</div>
                    </div>
                    <span class="mt-1 inline-block size-4 rounded-full border border-white/40"
                        style="background: {{ $selectedTemplate['primary_color'] ?? '#2563eb' }}"></span>
                </div>
                <flux:button type="button" variant="filled" size="sm" class="mt-3 w-full"
                    @click="showTemplateModal = true">
                    Cambiar plantilla
                </flux:button>
            </div>

            <div x-cloak x-show="showTemplateModal" x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/50 p-4"
                @keydown.escape.window="showTemplateModal = false">
                <div @click.away="showTemplateModal = false"
                    class="max-h-[80vh] w-full max-w-xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                    <div
                        class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Selecciona una plantilla
                            </h3>
                        </div>
                        <flux:button type="button" variant="filled" size="sm" @click="showTemplateModal = false">
                            Cerrar</flux:button>
                    </div>

                    <div class="max-h-[65vh] overflow-y-auto p-4">
                        <div class="grid gap-2">
                            @foreach ($availableTemplates as $template)
                                <button type="button" wire:click="selectTemplate('{{ $template['key'] }}')"
                                    @click="showTemplateModal = false"
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
                    </div>
                </div>
            </div>

            <div class="h-px bg-zinc-200 dark:bg-zinc-700"></div>

            <div class="space-y-2">
                <flux:heading size="sm">Bloques</flux:heading>
            </div>

            <flux:button type="button" variant="subtle" size="sm" class="w-full"
                @click="showAddBlockModal = true">
                Agregar bloque
            </flux:button>

            <div class="space-y-2">
                @foreach ($blocks as $block)
                    <button type="button" wire:click="selectBlock({{ $block['id'] }})"
                        class="w-full rounded-lg border px-3 py-2 text-left text-sm transition
                        {{ $selectedBlockId === $block['id']
                            ? 'border-brand-500 bg-brand-50 text-zinc-900 dark:border-brand-400 dark:bg-zinc-800 dark:text-zinc-100'
                            : 'border-zinc-200 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="font-medium">{{ $blockLabels[$block['block_type']] ?? ucfirst($block['block_type']) }}</span>
                            @if ($block['block_type'] === 'navbar')
                                <flux:badge color="blue" size="sm">Fijo</flux:badge>
                            @else
                                <flux:badge color="{{ $block['is_active'] ? 'green' : 'zinc' }}" size="sm">
                                    {{ $block['is_active'] ? 'Activo' : 'Inactivo' }}
                                </flux:badge>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>

            <div x-cloak x-show="showAddBlockModal" x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/50 p-4"
                @keydown.escape.window="showAddBlockModal = false">
                <div @click.away="showAddBlockModal = false"
                    class="max-h-[80vh] w-full max-w-xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                    <div
                        class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Agregar bloque</h3>
                            <p class="text-xs text-zinc-500">Selecciona el tipo de bloque que deseas incorporar.</p>
                        </div>
                        <flux:button type="button" variant="filled" size="sm" @click="showAddBlockModal = false">
                            Cerrar</flux:button>
                    </div>

                    <div class="max-h-[65vh] overflow-y-auto p-4">
                        <div class="grid gap-2">
                            @foreach ($availableBlocks as $blockType)
                                <button type="button" wire:click="addBlock('{{ $blockType }}')"
                                    @click="showAddBlockModal = false"
                                    class="w-full rounded-lg border border-zinc-200 p-3 text-left transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $blockLabels[$blockType] ?? ucfirst($blockType) }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="space-y-6">
            @if ($selectedBlockType !== 'navbar')
                <form id="landing-global-form" wire:submit="save"
                    class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
                    <flux:heading size="sm">Configuración global</flux:heading>
                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input wire:model="form.siteName" label="Nombre del sitio" placeholder="Mi Empresa" />
                        <flux:input wire:model="form.primaryColor" type="color" label="Color primario" />
                    </div>
                </form>
            @endif

            <div
                class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">Vista previa</flux:heading>
                    <a class="text-xs underline" target="_blank" href="{{ $previewUrl }}">Abrir preview</a>
                </div>

                <iframe wire:key="landing-preview-{{ $previewUrl }}" src="{{ $previewUrl }}"
                    class="h-135 w-full rounded-lg border border-zinc-200 dark:border-zinc-700"></iframe>
            </div>
        </div>

        <aside
            class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading level="3">
                Editor de bloque
                <i class="mt-2 ">Tipo: {{ $selectedBlockType ?: 'N/A' }}.</i>
            </flux:heading>

            <div class="flex items-center justify-between">


                @if ($selectedBlockType)
                    <flux:button.group>
                        <flux:button type="button" variant="filled" size="sm" wire:click="moveSelectedBlockUp"
                            icon="arrow-up">
                            Subir
                        </flux:button>
                        <flux:button type="button" variant="filled" size="sm"
                            wire:click="moveSelectedBlockDown" icon="arrow-down">
                            Bajar
                        </flux:button>
                        @if ($selectedBlockType !== 'navbar')
                            <flux:button type="button" variant="filled" size="sm" wire:click="toggleBlock"
                                icon="{{ $editingBlockActive ? 'eye-slash' : 'eye' }}">
                                {{ $editingBlockActive ? 'Desactivar' : 'Activar' }}
                            </flux:button>
                            <flux:button type="button" variant="danger" size="sm"
                                wire:click="removeSelectedBlock" wire:confirm="¿Eliminar este bloque?"
                                icon="trash">
                                Quitar
                            </flux:button>
                        @endif
                    </flux:button.group>
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
