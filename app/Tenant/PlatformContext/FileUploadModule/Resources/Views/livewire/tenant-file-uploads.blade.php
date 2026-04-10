<section class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Archivos del tenant</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            Sube archivos seguros y aislados usando el disk tenant.
        </p>
    </div>

    @if ($successMessage)
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="upload"
        class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:input type="file" wire:model="form.file" label="Archivo" />
            <flux:input wire:model="form.folder" label="Carpeta (opcional)" placeholder="contratos" />
        </div>

        @error('form.file')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Subir archivo</flux:button>
        </div>
    </form>

    <div
        class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model.live.debounce.300ms="search" label="Buscar" placeholder="nombre o mime" />
            <flux:select wire:model.live="perPage" label="Resultados por página">
                <flux:select.option value="10">10</flux:select.option>
                <flux:select.option value="20">20</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
            </flux:select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Nombre</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">MIME</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Tamaño</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Ruta</th>
                        <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($files as $file)
                        <tr class="border-t border-zinc-100 dark:border-zinc-800">
                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $file->original_name }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $file->mime_type ?? 'n/a' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ number_format((int) $file->size_bytes / 1024, 2) }} KB</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $file->stored_path }}</td>
                            <td class="px-4 py-3 text-right">
                                <flux:button variant="danger" wire:click="confirmDelete({{ $file->id }})">Eliminar
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                No hay archivos subidos para este tenant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $files->links() }}
        </div>
    </div>

    @if ($fileIdToDelete)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950">
            <p class="text-sm text-red-700 dark:text-red-300">¿Confirmas eliminar este archivo?</p>
            <div class="mt-3 flex gap-2">
                <flux:button variant="danger" wire:click="deleteConfirmed">Sí, eliminar</flux:button>
                <flux:button variant="ghost" wire:click="cancelDelete">Cancelar</flux:button>
            </div>
        </div>
    @endif
</section>
