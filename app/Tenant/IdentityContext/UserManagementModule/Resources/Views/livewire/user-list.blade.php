<div class="space-y-6">

    {{-- Cabecera --}}
    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Gestión de Usuarios') }}</flux:heading>
            <flux:subheading>{{ __('Administra los usuarios y sus roles dentro del workspace.') }}</flux:subheading>
        </div>

        @can('create', \App\Tenant\IdentityContext\AuthenticationModule\Models\User::class)
            <flux:button wire:click="openCreateModal" variant="primary" icon="user-plus">
                {{ __('Nuevo usuario') }}
            </flux:button>
        @endcan
    </section>

    {{-- Buscador --}}
    <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Buscar por nombre o email…') }}"
        icon="magnifying-glass" />

    {{-- Tabla --}}
    <flux:card>
        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Usuario') }}
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Rol') }}
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Estado') }}
                        </th>
                        <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                    @forelse ($users as $user)
                        <tr wire:key="user-row-{{ $user->id }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                            </td>

                            <td class="px-4 py-3">
                                @php $roleName = $user->roles->first()?->name; @endphp
                                @if ($roleName)
                                    <flux:badge
                                        color="{{ match ($roleName) {'admin' => 'red','manager' => 'blue',default => 'zinc'} }}"
                                        size="sm">
                                        {{ \App\Tenant\UserManagementModule\Enums\TenantRole::from($roleName)->label() }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Sin rol') }}</flux:badge>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @php $status = $user->status instanceof \App\Tenant\UserManagementModule\Enums\TenantUserStatus ? $user->status : \App\Tenant\UserManagementModule\Enums\TenantUserStatus::from((string)$user->status); @endphp
                                <flux:badge
                                    color="{{ $status === \App\Tenant\UserManagementModule\Enums\TenantUserStatus::Active ? 'green' : 'zinc' }}"
                                    size="sm">
                                    {{ $status->label() }}
                                </flux:badge>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @can('update', $user)
                                        <flux:button wire:click="openEditModal({{ $user->id }})" variant="ghost"
                                            size="sm" icon="pencil" />
                                    @endcan

                                    @can('delete', $user)
                                        <flux:button wire:click="confirmDelete({{ $user->id }})" variant="ghost"
                                            size="sm" icon="trash" class="text-red-500 hover:text-red-700" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-500">
                                {{ __('No se encontraron usuarios.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </flux:card>

    {{-- Modal crear/editar --}}
    <flux:modal wire:model="showModal" class="w-full max-w-md">
        <div class="space-y-5">
            <flux:heading size="lg">
                {{ $form->editingId ? __('Editar usuario') : __('Nuevo usuario') }}
            </flux:heading>

            <form wire:submit="save">
                <div class="space-y-4">
                    <flux:input wire:model="form.name" label="{{ __('Nombre') }}"
                        placeholder="{{ __('Nombre completo') }}" required />

                    <flux:input wire:model="form.email" label="{{ __('Email') }}" type="email"
                        placeholder="usuario@empresa.com" required />

                    <flux:input wire:model="form.password"
                        label="{{ $form->editingId ? __('Nueva contraseña (opcional)') : __('Contraseña') }}"
                        type="password" placeholder="••••••••" :required="! $form->editingId" />

                    <flux:select wire:model="form.role" label="{{ __('Rol') }}">
                        @foreach ($roles as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @if ($form->editingId)
                        <flux:select wire:model="form.status" label="{{ __('Estado') }}">
                            @foreach ($statuses as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $form->editingId ? __('Guardar cambios') : __('Crear usuario') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Modal confirmar eliminación --}}
    @if ($deletingId)
        <flux:modal :show="(bool) $deletingId" class="w-full max-w-sm">
            <div class="space-y-4">
                <flux:heading size="lg">{{ __('Eliminar usuario') }}</flux:heading>
                <flux:text>
                    {{ __('¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.') }}
                </flux:text>

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="cancelDelete" variant="ghost">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="destroyConfirmed" variant="danger">{{ __('Eliminar') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

</div>
