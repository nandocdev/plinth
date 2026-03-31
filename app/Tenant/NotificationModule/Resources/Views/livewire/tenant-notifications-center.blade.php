<section class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Notificaciones del tenant</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            Envia avisos por email y canal database a usuarios de este tenant.
        </p>
    </div>

    @if ($successMessage)
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="send"
        class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="form.subject" label="Asunto" placeholder="Mantenimiento programado" />
            <flux:select wire:model="form.targetRole" label="Destinatarios">
                <flux:select.option value="all">Todos</flux:select.option>
                <flux:select.option value="admin">Admins</flux:select.option>
                <flux:select.option value="manager">Managers</flux:select.option>
                <flux:select.option value="member">Members</flux:select.option>
            </flux:select>
        </div>

        <flux:textarea wire:model="form.message" label="Mensaje" rows="4"
            placeholder="Detalle de la notificación..." />

        @error('form.subject')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('form.message')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('form.targetRole')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="paper-airplane">Enviar notificación</flux:button>
        </div>
    </form>

    <div
        class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-zinc-800 dark:text-zinc-100">Inbox</h2>
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
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Asunto</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Mensaje</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Estado</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Fecha</th>
                        <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr class="border-t border-zinc-100 dark:border-zinc-800">
                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                                {{ $notification->data['subject'] ?? 'Sin asunto' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $notification->data['message'] ?? 'Sin mensaje' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                @if ($notification->read_at)
                                    <flux:badge color="emerald" size="sm">Leida</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">No leida</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $notification->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                @if (!$notification->read_at)
                                    <flux:button variant="ghost" wire:click="markAsRead('{{ $notification->id }}')">
                                        Marcar leida</flux:button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                No hay notificaciones disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $notifications->links() }}
        </div>
    </div>
</section>
