<div class="space-y-6">

    {{-- Cabecera --}}
    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Gestión de Usuarios') }}</flux:heading>
            <flux:subheading>{{ __('Administra los usuarios y sus roles dentro del workspace.') }}</flux:subheading>
        </div>

        @can('create', \App\Tenant\AuthenticationModule\Models\User::class)
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
        <flux:table>
            <flux:columns>
                <flux:column>{{ __('Usuario') }}</flux:column>
                <flux:column>{{ __('Rol') }}</flux:column>
                <flux:column>{{ __('Estado') }}</flux:column>
                <flux:column></flux:column>
            </flux:columns>

            <flux:rows>
                @forelse ($users as $user)
                    <flux:row :key="$user->id">
                        <flux:cell>
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                            </div>
                        </flux:cell>

                        <flux:cell>
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
                        </flux:cell>

                        <flux:cell>
                            @php $status = $user->status instanceof \App\Tenant\UserManagementModule\Enums\TenantUserStatus ? $user->status : \App\Tenant\UserManagementModule\Enums\TenantUserStatus::from((string)$user->status); @endphp
                            <flux:badge
                                color="{{ $status === \App\Tenant\UserManagementModule\Enums\TenantUserStatus::Active ? 'green' : 'zinc' }}"
                                size="sm">
                                {{ $status->label() }}
                            </flux:badge>
                        </flux:cell>

                        <flux:cell>
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
                        </flux:cell>
                    </flux:row>
                @empty
                    <flux:row>
                        <flux:cell colspan="4">
                            <p class="py-6 text-center text-sm text-zinc-500">{{ __('No se encontraron usuarios.') }}
                            </p>
                        </flux:cell>
                    </flux:row>
                @endforelse
            </flux:rows>
        </flux:table>

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
                            <flux:option value="{{ $value }}">{{ $label }}</flux:option>
                        @endforeach
                    </flux:select>

                    @if ($form->editingId)
                        <flux:select wire:model="form.status" label="{{ __('Estado') }}">
                            @foreach ($statuses as $value => $label)
                                <flux:option value="{{ $value }}">{{ $label }}</flux:option>
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
