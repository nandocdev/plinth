<div class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="xl">{{ __('System health') }}</flux:heading>
        <flux:subheading>{{ __('Monitorea conexiones de DB, estado de cola y uso de storage en tiempo real.') }}</flux:subheading>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div>
                <label for="health-connection" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Connection filter') }}
                </label>
                <select id="health-connection" wire:model.live="filterForm.connectionFilter"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="all">all</option>
                    <option value="central">central</option>
                    <option value="tenant_template">tenant_template</option>
                </select>
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" wire:model.live="filterForm.onlyUnhealthy"
                        class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800" />
                    {{ __('Only unhealthy connections') }}
                </label>
            </div>

            <div class="flex items-end justify-end">
                <flux:button wire:click="clearFilters" variant="filled">{{ __('Clear filters') }}</flux:button>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Database connections') }}</flux:heading>
            <div class="mt-4 space-y-3">
                @forelse ($snapshot->connections as $connection)
                    <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <span class="font-mono">{{ $connection->name }}</span>
                            <span class="rounded-full px-2 py-1 text-xs {{ $connection->ok ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $connection->status }}
                            </span>
                        </div>
                        @if ($connection->error)
                            <p class="mt-2 break-all text-xs text-red-600 dark:text-red-400">{{ $connection->error }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">{{ __('No connections for current filter.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Queue status') }}</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <p><span class="font-semibold">{{ __('Connection:') }}</span> {{ $snapshot->queue->connection }}</p>
                <p><span class="font-semibold">{{ __('Pending jobs:') }}</span> {{ $snapshot->queue->pendingJobs }}</p>
                <p><span class="font-semibold">{{ __('Failed jobs:') }}</span> {{ $snapshot->queue->failedJobs }}</p>
                <p>
                    <span class="font-semibold">{{ __('Health:') }}</span>
                    <span class="rounded-full px-2 py-1 text-xs {{ $snapshot->queue->healthy ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                        {{ $snapshot->queue->healthy ? 'ok' : 'warning' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Storage usage') }}</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <p><span class="font-semibold">app:</span> {{ number_format($snapshot->storage->appBytes) }} B</p>
                <p><span class="font-semibold">logs:</span> {{ number_format($snapshot->storage->logsBytes) }} B</p>
                <p><span class="font-semibold">framework:</span> {{ number_format($snapshot->storage->frameworkBytes) }} B</p>
                <p><span class="font-semibold">total:</span> {{ $snapshot->storage->formattedTotal }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-4 text-xs text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
        {{ __('Snapshot generated at: :time', ['time' => $snapshot->generatedAt]) }}
    </div>
</div>
