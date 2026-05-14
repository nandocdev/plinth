<div class="space-y-6">
    {{-- Header --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Roles de Administradores') }}</flux:heading>
                <flux:subheading>{{ __('Asigna roles y permisos a los administradores del panel central.') }}
                </flux:subheading>
            </div>
        </div>

        @if (session('status'))
            <flux:text class="mt-4 text-green-600 dark:text-green-400">{{ session('status') }}</flux:text>
        @endif
    </div>

    {{-- Tabla de admins --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <flux:input wire:model.live.debounce.400ms="search" :label="__('Buscar admin')"
                :placeholder="__('Nombre o email')" class="md:max-w-sm" />
            <flux:text>{{ __('Total: :count', ['count' => $admins->total()]) }}</flux:text>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 pr-4">{{ __('Admin') }}</th>
                        <th class="py-3 pr-4">{{ __('Email verificado') }}</th>
                        <th class="py-3 pr-4">{{ __('Rol actual') }}</th>
                        <th class="py-3 pr-4">{{ __('Permisos') }}</th>
                        <th class="py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $admin)
                        @php $currentRole = $admin->roles->first() @endphp
                        <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="admin-{{ $admin->id }}">
                            <td class="py-3 pr-4">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $admin->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $admin->email }}</div>
                            </td>
                            <td class="py-3 pr-4">
                                @if ($admin->email_verified_at)
                                    <flux:badge variant="solid" color="emerald" size="sm">{{ __('Verificado') }}
                                    </flux:badge>
                                @else
                                    <flux:badge variant="solid" color="zinc" size="sm">{{ __('Sin verificar') }}
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                @if ($currentRole)
                                    <flux:badge variant="solid" color="blue" size="sm">
                                        {{ \App\Central\AdminAuthorizationModule\Enums\AdminRole::from($currentRole->name)->label() }}
                                    </flux:badge>
                                @else
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ __('Sin rol') }}</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($admin->permissions as $permission)
                                        <flux:badge color="zinc" size="sm">{{ $permission->name }}</flux:badge>
                                    @endforeach
                                    @if ($admin->permissions->isEmpty() && $currentRole)
                                        {{-- Permisos via rol --}}
                                        @foreach ($admin->getPermissionsViaRoles() as $permission)
                                            <flux:badge color="zinc" size="sm" variant="outline">
                                                {{ $permission->name }}</flux:badge>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('admin-roles.assign')
                                        <flux:button size="sm" variant="filled"
                                            wire:click="openAssignModal({{ $admin->id }})">
                                            {{ __('Asignar rol') }}
                                        </flux:button>
                                    @endcan

                                    @if ($currentRole)
                                        @can('admin-roles.revoke')
                                            <flux:button size="sm" variant="danger"
                                                wire:click="revokeRole({{ $admin->id }})"
                                                wire:confirm="{{ __('¿Quitar rol de :name?', ['name' => $admin->name]) }}">
                                                {{ __('Revocar') }}
                                            </flux:button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                {{ __('No se encontraron administradores.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $admins->links() }}
        </div>
    </div>

    {{-- Modal asignar rol --}}
    <flux:modal wire:model="showAssignModal" name="assign-role-modal" class="md:w-96">
        <div class="space-y-4">
            <flux:heading>{{ __('Asignar rol de administrador') }}</flux:heading>
            <flux:subheading>{{ __('El rol reemplazará cualquier rol existente del administrador.') }}
            </flux:subheading>

            <form wire:submit="assignRole" class="space-y-4">
                <flux:select wire:model="form.role" :label="__('Rol')">
                    <flux:select.option value="" disabled>{{ __('Seleccionar rol...') }}</flux:select.option>
                    @foreach ($allRoles as $role)
                        <flux:select.option value="{{ $role->value }}">
                            {{ $role->label() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                @error('form.role')
                    <flux:text class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</flux:text>
                @enderror

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ __('Asignar') }}</flux:button>
                    <flux:button type="button" wire:click="$set('showAssignModal', false)" variant="filled">
                        {{ __('Cancelar') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
