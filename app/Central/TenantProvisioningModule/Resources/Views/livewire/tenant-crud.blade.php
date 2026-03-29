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

            <form wire:submit="createDomain" class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <label for="domain-tenant" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                        {{ __('Tenant') }}
                    </label>
                    <select id="domain-tenant" wire:model="domainForm.tenantId"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                        required>
                        <option value="">{{ __('Select tenant') }}</option>
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->displayName() }}</option>
                        @endforeach
                    </select>
                    @error('domainForm.tenantId')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <flux:input wire:model="domainForm.domain" :label="__('Custom domain')"
                    :placeholder="__('workspace.example.com')" required />

                <div class="flex items-end">
                    <flux:button type="submit" variant="primary" class="w-full md:w-auto">
                        {{ __('Add domain') }}
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
                            <th class="py-3 pr-3">{{ __('Domains') }}</th>
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
                                <td class="py-3 pr-3">
                                    <div class="space-y-2">
                                        @forelse ($tenant->domains as $domain)
                                            <div
                                                class="rounded-lg border border-zinc-200 px-2 py-1 dark:border-zinc-700">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-mono text-xs">{{ $domain->domain }}</span>

                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="rounded-full px-2 py-1 text-xs {{ $domain->verified_at ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                                            {{ $domain->verified_at ? __('Verified') : __('Pending') }}
                                                        </span>

                                                        <flux:button
                                                            wire:click="verifyDomain('{{ $tenant->id }}', {{ $domain->id }})"
                                                            variant="ghost" size="sm">
                                                            {{ $domain->verified_at ? __('Unverify') : __('Verify') }}
                                                        </flux:button>

                                                        <flux:button
                                                            wire:click="deleteDomain('{{ $tenant->id }}', {{ $domain->id }})"
                                                            wire:confirm="{{ __('This will remove the selected domain. Continue?') }}"
                                                            variant="danger" size="sm"
                                                            @disabled($tenant->domains->count() <= 1)>
                                                            {{ __('Delete') }}
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <span class="text-xs text-zinc-500">{{ __('No domains assigned.') }}</span>
                                        @endforelse
                                    </div>
                                </td>
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
