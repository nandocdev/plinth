<div class="flex flex-col gap-6">
    {{-- Header de la Página --}}
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Gestión de Tenants</flux:heading>
            <flux:subheading>Administra los espacios de trabajo, dominios y estado operativo de tus clientes.
            </flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('central.tenants.onboarding') }}" wire:navigate variant="primary" icon="plus"
                color="orange">
                Nuevo Tenant
            </flux:button>
        </div>
    </header>

    @if (session('status'))
        <flux:card class="bg-green-50 dark:bg-green-950/20 border-green-200 dark:border-green-900/50 py-3 px-4">
            <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                <flux:icon name="check-circle" variant="micro" />
                <p class="text-sm font-medium">{{ session('status') }}</p>
            </div>
        </flux:card>
    @endif

    {{-- Tabla de Tenants --}}
    <flux:card class=" overflow-hidden">
        <div
            class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por nombre, ID o dominio..."
                icon="magnifying-glass" class="max-w-md" />

            <div class="flex items-center gap-4">
                <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $tenants->total() }}</b></flux:text>
                <flux:separator vertical class="h-4" />
                <div class="flex gap-2">
                    <flux:modal.trigger name="add-domain-modal">
                        <flux:button variant="ghost" size="sm" icon="globe-alt">Agregar Dominio</flux:button>
                    </flux:modal.trigger>
                    <flux:modal.trigger name="branding-modal">
                        <flux:button variant="ghost" size="sm" icon="paint-brush">Personalizar Branding
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Cliente / ID</flux:table.column>
                <flux:table.column>Plan & Región</flux:table.column>
                <flux:table.column>Infraestructura</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($tenants as $tenant)
                    <flux:table.row :key="$tenant->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span
                                    class="font-bold text-zinc-900 dark:text-zinc-100">{{ $tenant->displayName() }}</span>
                                <span class="text-xs font-mono text-zinc-500">{{ $tenant->id }}</span>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach ($tenant->domains as $domain)
                                        <flux:badge size="sm" variant="outline"
                                            color="{{ $domain->verified_at ? 'zinc' : 'amber' }}"
                                            class="text-[10px] py-0">
                                            {{ $domain->domain }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-1">
                                <flux:badge size="sm" color="blue" variant="subtle">
                                    {{ $tenant->subscription?->plan?->name ?? 'Sin Plan' }}</flux:badge>
                                <div class="flex items-center gap-1 text-xs text-zinc-500">
                                    <flux:icon name="map-pin" variant="micro" class="size-3" />
                                    {{ $tenant->region() }}
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-1 text-xs text-zinc-600 dark:text-zinc-400">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="circle-stack" variant="micro" class="size-3" />
                                    <span>{{ $tenant->completed_backups_count ?? 0 }} Backups</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:icon name="clock" variant="micro" class="size-3" />
                                    <span>{{ $tenant->latest_backup_completed_at ? \Illuminate\Support\Carbon::parse($tenant->latest_backup_completed_at)->diffForHumans() : 'Nunca' }}</span>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm"
                                color="{{ $tenant->status() === 'suspended' ? 'red' : 'green' }}" inset="top">
                                {{ strtoupper($tenant->status()) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="impersonateTenant('{{ $tenant->id }}')" variant="ghost"
                                    size="sm" icon="finger-print" tooltip="Impersonar">
                                    Acceder
                                </flux:button>

                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />

                                    <flux:menu>
                                        <flux:menu.item wire:click="suspendTenant('{{ $tenant->id }}')"
                                            icon="{{ $tenant->status() === 'suspended' ? 'play' : 'pause' }}">
                                            {{ $tenant->status() === 'suspended' ? 'Reactivar' : 'Suspender' }}
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        <flux:menu.item wire:click="queueBackup('{{ $tenant->id }}')"
                                            icon="arrow-path">
                                            Crear Backup Ahora
                                        </flux:menu.item>

                                        <flux:menu.item
                                            wire:click="restoreTenant('{{ $tenant->id }}', {{ (int) ($tenant->latest_backup_snapshot_id ?? 0) }})"
                                            icon="arrow-uturn-left"
                                            :disabled="empty($tenant->latest_backup_snapshot_id)">
                                            Restaurar Último Backup
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        <flux:menu.item wire:click="deleteTenant('{{ $tenant->id }}')"
                                            variant="danger" icon="trash"
                                            wire:confirm="¿Estás seguro de eliminar este tenant? Esta acción es irreversible.">
                                            Eliminar Permanentemente
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500 italic">
                            No se encontraron inquilinos con el criterio de búsqueda.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $tenants->links() }}
        </div>
    </flux:card>

    {{-- Modal: Agregar Dominio --}}
    <flux:modal name="add-domain-modal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Agregar Dominio Personalizado</flux:heading>
                <flux:subheading>Vincula un nuevo dominio a un espacio de trabajo existente.</flux:subheading>
            </div>

            <form wire:submit="createDomain" class="space-y-4">
                <flux:select wire:model="domainForm.tenantId" label="Seleccionar Tenant" required>
                    <option value="">-- Elegir Cliente --</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->displayName() }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="domainForm.domain" label="Dominio (ej: app.empresa.com)" required />

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Guardar Dominio</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Modal: Branding --}}
    <flux:modal name="branding-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Identidad Visual (Branding)</flux:heading>
                <flux:subheading>Personaliza colores y logotipos para la instancia del cliente.</flux:subheading>
            </div>

            <form wire:submit="updateBranding" class="space-y-4">
                <div class="flex items-end gap-2">
                    <flux:select wire:model="brandingForm.tenantId" label="Seleccionar Tenant" class="flex-1">
                        <option value="">-- Elegir Cliente --</option>
                        @foreach ($tenantBrandingOptions as $tenantOption)
                            <option value="{{ $tenantOption['id'] }}">{{ $tenantOption['name'] }}</option>
                        @endforeach
                    </flux:select>
                    <flux:button type="button" wire:click="loadBranding" variant="filled">Cargar</flux:button>
                </div>

                <flux:separator />

                <flux:input wire:model="brandingForm.brandName" label="Nombre Comercial" />
                <flux:input wire:model="brandingForm.logoUrl" label="URL del Logotipo" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="brandingForm.primaryColor" label="Color Primario" type="color" />
                    <flux:input wire:model="brandingForm.secondaryColor" label="Color Secundario" type="color" />
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cerrar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Actualizar Branding</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
