<x-layouts::app :title="__('Tenant Management')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="xl">{{ __('Tenants') }}</flux:heading>
            <flux:subheading>{{ __('Create, suspend and remove tenant workspaces from central context.') }}
            </flux:subheading>

            @if (session('status'))
                <flux:text class="mt-4 text-green-600 dark:text-green-400">{{ session('status') }}</flux:text>
            @endif

            <form wire:submit="createTenant" class="mt-6 grid gap-4 md:grid-cols-3">
                <flux:input wire:model="form.name" :label="__('Tenant name')" :placeholder="__('Acme Inc')" required />

                <flux:input wire:model="form.primaryDomain" :label="__('Primary domain')"
                    :placeholder="__('acme.localhost')" required />

                <div class="flex items-end">
                    <flux:button type="submit" variant="primary" class="w-full md:w-auto">
                        {{ __('Create tenant') }}
                    </flux:button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <flux:input wire:model.live.debounce.400ms="search" :label="__('Search tenants')"
                    :placeholder="__('Search by id or name')" class="md:max-w-sm" />

                <flux:text>{{ __('Total: :count', ['count' => $tenants->total()]) }}</flux:text>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="py-3 pr-3">{{ __('Name') }}</th>
                            <th class="py-3 pr-3">{{ __('Tenant ID') }}</th>
                            <th class="py-3 pr-3">{{ __('Domain') }}</th>
                            <th class="py-3 pr-3">{{ __('Status') }}</th>
                            <th class="py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenants as $tenant)
                            <tr wire:key="tenant-{{ $tenant->id }}"
                                class="border-b border-zinc-100 dark:border-zinc-800">
                                <td class="py-3 pr-3">{{ $tenant->displayName() }}</td>
                                <td class="py-3 pr-3 font-mono text-xs">{{ $tenant->id }}</td>
                                <td class="py-3 pr-3">{{ optional($tenant->domains->first())->domain ?? '-' }}</td>
                                <td class="py-3 pr-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs {{ $tenant->status() === 'suspended' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' }}">
                                        {{ $tenant->status() }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button wire:click="suspendTenant('{{ $tenant->id }}')" variant="filled"
                                            size="sm">
                                            {{ $tenant->status() === 'suspended' ? __('Unsuspend') : __('Suspend') }}
                                        </flux:button>

                                        <flux:button wire:click="deleteTenant('{{ $tenant->id }}')"
                                            wire:confirm="{{ __('This will delete tenant database resources. Continue?') }}"
                                            variant="danger" size="sm">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-zinc-500">{{ __('No tenants found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
