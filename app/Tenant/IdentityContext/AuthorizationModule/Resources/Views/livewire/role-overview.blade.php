<div class="space-y-6">

    {{-- Cabecera --}}
    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Roles & Permisos') }}</flux:heading>
            <flux:subheading>
                {{ __('Visualiza los roles del workspace y reasigna usuarios según sus responsabilidades.') }}
            </flux:subheading>
        </div>
    </section>

    {{-- Tarjetas por rol --}}
    <div class="grid gap-5 sm:grid-cols-1 lg:grid-cols-3">
        @foreach ($roles as $role)
            @php
                $tenantRole = \App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole::tryFrom($role->name);
            @endphp

            <flux:card class="flex flex-col gap-4">

                {{-- Cabecera de la tarjeta --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <flux:badge
                            color="{{ $tenantRole?->color() ?? 'zinc' }}"
                            size="sm">
                            {{ $tenantRole?->label() ?? $role->name }}
                        </flux:badge>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $role->users_count }}
                            {{ __('usuario') }}{{ $role->users_count !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                {{-- Descripción del rol --}}
                @if ($tenantRole)
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                        {{ $tenantRole->description() }}
                    </p>
                @endif

                <flux:separator />

                {{-- Lista de usuarios asignados al rol --}}
                @if ($role->users->isEmpty())
                    <p class="py-2 text-center text-xs text-zinc-400 dark:text-zinc-600">
                        {{ __('Sin usuarios asignados.') }}
                    </p>
                @else
                    <ul class="space-y-3">
                        @foreach ($role->users as $user)
                            <li wire:key="role-user-{{ $role->name }}-{{ $user->id }}"
                                class="flex items-center justify-between gap-2">

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $user->name }}
                                    </p>
                                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $user->email }}
                                    </p>
                                </div>

                                @if ($canManage)
                                    @if ($reassigningUserId === $user->id)
                                        {{-- Formulario inline de reasignación --}}
                                        <div class="flex shrink-0 items-center gap-1">
                                            <flux:select
                                                wire:model="reassignRole"
                                                size="sm"
                                                class="w-32">
                                                @foreach (\App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole::cases() as $case)
                                                    <flux:select.option value="{{ $case->value }}">
                                                        {{ $case->label() }}
                                                    </flux:select.option>
                                                @endforeach
                                            </flux:select>
                                            <flux:button
                                                wire:click="confirmReassign"
                                                variant="primary"
                                                size="sm"
                                                icon="check"
                                                wire:loading.attr="disabled" />
                                            <flux:button
                                                wire:click="cancelReassign"
                                                variant="ghost"
                                                size="sm"
                                                icon="x-mark" />
                                        </div>
                                    @else
                                        <flux:button
                                            wire:click="startReassign({{ $user->id }}, '{{ $role->name }}')"
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil-square"
                                            title="{{ __('Cambiar rol') }}" />
                                    @endif
                                @endif

                            </li>
                        @endforeach
                    </ul>
                @endif

            </flux:card>
        @endforeach
    </div>

    {{-- Leyenda de permisos por rol --}}
    <flux:card class="mt-2">
        <flux:heading size="sm" class="mb-3">{{ __('Matriz de capacidades') }}</flux:heading>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-2 pe-4 text-left font-medium text-zinc-500 dark:text-zinc-400">
                            {{ __('Capacidad') }}
                        </th>
                        @foreach (\App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole::cases() as $case)
                            <th class="py-2 px-3 text-center font-medium text-zinc-500 dark:text-zinc-400">
                                {{ $case->label() }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @php
                        $matrix = [
                            'Gestión de usuarios'          => [true,  false, false],
                            'Gestión de roles'             => [true,  false, false],
                            'Configuración del workspace'  => [true,  false, false],
                            'Facturación y plan'           => [true,  false, false],
                            'Dominios personalizados'      => [true,  true,  false],
                            'Operaciones (logs, webhooks)' => [true,  true,  false],
                            'Módulos funcionales'          => [true,  true,  true],
                            'Notificaciones propias'       => [true,  true,  true],
                        ];
                    @endphp
                    @foreach ($matrix as $capability => $access)
                        <tr>
                            <td class="py-2 pe-4 text-zinc-700 dark:text-zinc-300">{{ $capability }}</td>
                            @foreach ($access as $allowed)
                                <td class="py-2 px-3 text-center">
                                    @if ($allowed)
                                        <flux:icon name="check-circle" class="mx-auto size-4 text-green-500" />
                                    @else
                                        <flux:icon name="minus-circle" class="mx-auto size-4 text-zinc-300 dark:text-zinc-600" />
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>

</div>
