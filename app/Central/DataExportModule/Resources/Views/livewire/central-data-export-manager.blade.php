<div class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div>
            <flux:heading size="xl">{{ __('GDPR Data Exports') }}</flux:heading>
            <flux:subheading>
                {{ __('Genera paquetes ZIP con datos centrales del tenant para solicitudes de acceso o portabilidad.') }}
            </flux:subheading>
        </div>

        @if (session('status'))
            <flux:text class="mt-4 text-green-600 dark:text-green-400">{{ session('status') }}</flux:text>
        @endif

        @if (session('error'))
            <flux:text class="mt-4 text-red-600 dark:text-red-400">{{ session('error') }}</flux:text>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg">{{ __('Solicitar nueva exportacion') }}</flux:heading>

        <form wire:submit="requestExport" class="mt-4 grid gap-4 md:grid-cols-2">
            <flux:select wire:model="form.tenantId" :label="__('Tenant')">
                <flux:select.option value="" disabled>{{ __('Seleccionar tenant...') }}</flux:select.option>
                @foreach ($tenantOptions as $tenant)
                    <flux:select.option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex items-end">
                <flux:checkbox wire:model="form.includeActivityLog" :label="__('Incluir activity log central del tenant')" />
            </div>

            <div class="md:col-span-2">
                <flux:button type="submit" variant="primary">{{ __('Generar exportacion GDPR') }}</flux:button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">{{ __('Historial de exportaciones') }}</flux:heading>
            <flux:text>{{ __('Exports: :count', ['count' => $exports->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-3">{{ __('Fecha') }}</th>
                        <th class="py-3 pr-3">{{ __('Tenant') }}</th>
                        <th class="py-3 pr-3">{{ __('Solicitado por') }}</th>
                        <th class="py-3 pr-3">{{ __('Estado') }}</th>
                        <th class="py-3 pr-3">{{ __('Incluye logs') }}</th>
                        <th class="py-3 pr-3">{{ __('Tamano') }}</th>
                        <th class="py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exports as $export)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3 pr-3 font-mono text-xs">{{ $export->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 pr-3">{{ $export->tenant?->displayName() ?? $export->tenant_id }}</td>
                            <td class="py-3 pr-3">{{ $export->requestedBy?->email ?? '-' }}</td>
                            <td class="py-3 pr-3">
                                <flux:badge size="sm" color="zinc">{{ $export->status }}</flux:badge>
                            </td>
                            <td class="py-3 pr-3">{{ $export->include_activity_log ? __('Si') : __('No') }}</td>
                            <td class="py-3 pr-3">{{ $export->size_bytes !== null ? number_format((int) $export->size_bytes) . ' B' : '-' }}</td>
                            <td class="py-3 text-right">
                                @if ($export->status === \App\Central\DataExportModule\Models\CentralDataExport::STATUS_COMPLETED)
                                    <flux:button size="sm" variant="filled" :href="route('central.exports.download', $export)">
                                        {{ __('Descargar ZIP') }}
                                    </flux:button>
                                @elseif ($export->status === \App\Central\DataExportModule\Models\CentralDataExport::STATUS_FAILED)
                                    <flux:text class="text-red-600 dark:text-red-400">{{ $export->error_message }}</flux:text>
                                @else
                                    <flux:text>{{ __('En progreso') }}</flux:text>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-zinc-500">
                                {{ __('No hay exportaciones GDPR registradas.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $exports->links() }}
        </div>
    </div>
</div>