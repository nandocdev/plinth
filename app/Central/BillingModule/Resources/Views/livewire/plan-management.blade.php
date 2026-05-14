<div class="flex flex-col gap-6">
    <header class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Gestión de Planes</flux:heading>
            <flux:subheading>Define los niveles de servicio, precios y límites de recursos para tus clientes.
            </flux:subheading>
        </div>
        <flux:modal.trigger name="plan-modal">
            <flux:button variant="primary" icon="plus" color="indigo">Nuevo Plan</flux:button>
        </flux:modal.trigger>
    </header>

    @if (session('status'))
        <flux:card class="bg-green-50 dark:bg-green-950/20 border-green-200 dark:border-green-900/50 py-3 px-4">
            <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                <flux:icon name="check-circle" variant="micro" />
                <p class="text-sm font-medium">{{ session('status') }}</p>
            </div>
        </flux:card>
    @endif

    <flux:card class="overflow-hidden">
        <div
            class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Buscar por nombre o slug..."
                icon="magnifying-glass" class="max-w-md" />
            <flux:text size="sm" class="text-zinc-500">Total: <b>{{ $plans->total() }}</b></flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Plan / Slug</flux:table.column>
                <flux:table.column>Precios (USD)</flux:table.column>
                <flux:table.column>Límites (Soft / Hard)</flux:table.column>
                <flux:table.column>Suscripciones</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($plans as $plan)
                    <flux:table.row :key="$plan->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $plan->name }}</span>
                                <span class="text-xs font-mono text-zinc-500">{{ $plan->slug }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col text-xs">
                                <span>Mensual: <b>${{ number_format($plan->price_monthly_cents / 100, 2) }}</b></span>
                                <span class="text-zinc-500 italic">Anual:
                                    ${{ $plan->price_yearly_cents ? number_format($plan->price_yearly_cents / 100, 2) : '-' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col text-[10px] text-zinc-600 dark:text-zinc-400">
                                <span>Usuarios: {{ $plan->max_users_soft ?? '∞' }} /
                                    {{ $plan->max_users_hard ?? '∞' }}</span>
                                <span>Storage:
                                    {{ $plan->max_storage_mb_soft ? $plan->max_storage_mb_soft . 'MB' : '∞' }} /
                                    {{ $plan->max_storage_mb_hard ? $plan->max_storage_mb_hard . 'MB' : '∞' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" variant="subtle" color="zinc">{{ $plan->subscriptions_count }}
                                activas</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $plan->is_active ? 'green' : 'zinc' }}"
                                inset="top">
                                {{ $plan->is_active ? 'ACTIVO' : 'INACTIVO' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="plan-modal">
                                    <flux:button wire:click="startPlanEditing({{ $plan->id }})" variant="ghost"
                                        size="sm" icon="pencil-square" />
                                </flux:modal.trigger>
                                <flux:button wire:click="deletePlan({{ $plan->id }})" variant="ghost"
                                    size="sm" icon="trash" color="red"
                                    wire:confirm="¿Eliminar este plan? Esto puede afectar a nuevas suscripciones." />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-12 text-center text-zinc-500 italic">
                            No se encontraron planes.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
            {{ $plans->links() }}
        </div>
    </flux:card>

    <flux:modal name="plan-modal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingPlanId ? 'Editar Plan' : 'Crear Nuevo Plan' }}</flux:heading>
                <flux:subheading>Configura las reglas de negocio y precios para este nivel de servicio.
                </flux:subheading>
            </div>

            <form wire:submit="{{ $editingPlanId ? 'updatePlan' : 'createPlan' }}" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="planForm.name" label="Nombre del Plan" placeholder="Ej: Enterprise"
                        required />
                    <flux:input wire:model="planForm.slug" label="Slug (URL Friendly)" placeholder="enterprise-monthly"
                        required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="planForm.priceMonthlyCents" type="number" label="Precio Mensual (centavos)"
                        required />
                    <flux:input wire:model="planForm.priceYearlyCents" type="number" label="Precio Anual (centavos)" />
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <flux:input wire:model="planForm.trialDays" type="number" label="Días de Trial" required />
                    <flux:input wire:model="planForm.sortOrder" type="number" label="Orden (Prioridad)" required />
                    <div class="flex items-center h-full pt-6">
                        <flux:checkbox wire:model="planForm.isActive" label="Plan Activo" />
                    </div>
                </div>

                <flux:separator />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="planForm.maxUsersSoft" type="number" label="Límite Soft Usuarios" />
                    <flux:input wire:model="planForm.maxUsersHard" type="number" label="Límite Hard Usuarios" />
                    <flux:input wire:model="planForm.maxStorageMbSoft" type="number"
                        label="Límite Soft Storage (MB)" />
                    <flux:input wire:model="planForm.maxStorageMbHard" type="number"
                        label="Límite Hard Storage (MB)" />
                </div>

                <flux:input wire:model="planForm.features" label="Características (separadas por coma)"
                    placeholder="api_access, support_24_7" />

                <div class="flex justify-end gap-2 pt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">
                        {{ $editingPlanId ? 'Guardar Cambios' : 'Crear Plan' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
