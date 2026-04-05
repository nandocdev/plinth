<section class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Export / Import CSV</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            Gestiona exportaciones e importaciones basicas de usuarios del tenant mediante jobs aislados por contexto.
        </p>
    </div>

    @if ($successMessage)
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
            {{ $successMessage }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="queueExport"
            class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Exportar usuarios a CSV</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Genera un archivo con columnas name, email y created_at para el tenant actual.
            </p>
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="arrow-down-tray">Encolar export</flux:button>
            </div>
        </form>

        <form wire:submit="queueImport"
            class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Importar usuarios desde CSV</h2>
            <flux:input type="file" wire:model="form.file" label="Archivo CSV" />
            @error('form.file')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="arrow-up-tray">Encolar import</flux:button>
            </div>
        </form>
    </div>

    <div
        class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-zinc-800 dark:text-zinc-100">Historial de ejecuciones</h2>
            <flux:select wire:model.live="perPage" label="Resultados" size="sm">
                <flux:select.option value="10">10</flux:select.option>
                <flux:select.option value="20">20</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
            </flux:select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Tipo</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Estado</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Filas</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Resultado</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr class="border-t border-zinc-100 dark:border-zinc-800">
                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ strtoupper($run->type) }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $run->status }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $run->processed_rows }} / {{ $run->total_rows }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $run->result_path ?? ($run->error_message ?? 'Pendiente') }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $run->created_at?->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                Aun no hay transferencias CSV para este tenant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $runs->links() }}
        </div>
    </div>
</section>
