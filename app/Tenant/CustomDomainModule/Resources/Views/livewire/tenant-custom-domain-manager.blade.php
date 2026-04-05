<div class="mx-auto max-w-6xl space-y-6 px-4 py-8">

    @if ($successMessage)
        <flux:callout variant="success" icon="check-circle">{{ $successMessage }}</flux:callout>
    @endif
    @if ($errorMessage)
        <flux:callout variant="danger" icon="exclamation-triangle">{{ $errorMessage }}</flux:callout>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Dominios personalizados</flux:heading>
            <flux:subheading>Gestiona dominios custom y solicita SSL automático (Let's Encrypt).</flux:subheading>
        </div>
    </div>

    @can('tenant.custom-domains.manage')
        <flux:card class="space-y-4">
            <flux:heading size="lg">Agregar dominio</flux:heading>

            <form wire:submit="create" class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_auto] md:items-end">
                <flux:field>
                    <flux:label>Dominio</flux:label>
                    <flux:input wire:model="domainForm.domain" placeholder="workspace.example.com" />
                    <flux:error name="domainForm.domain" />
                </flux:field>

                <flux:button type="submit" variant="primary" icon="plus">Agregar</flux:button>
            </form>
        </flux:card>
    @endcan

    <flux:card>
        <flux:heading size="lg" class="mb-4">Dominios registrados</flux:heading>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead>
                    <tr class="text-left text-zinc-500 dark:text-zinc-400">
                        <th class="px-3 py-2">Dominio</th>
                        <th class="px-3 py-2">Verificación DNS</th>
                        <th class="px-3 py-2">SSL</th>
                        <th class="px-3 py-2">Expira</th>
                        <th class="px-3 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($domains as $domain)
                        <tr>
                            <td class="px-3 py-2 font-medium text-zinc-900 dark:text-zinc-100">{{ $domain->domain }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-1 text-xs {{ $domain->verified_at ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ $domain->verified_at ? 'Verificado' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-1 text-xs {{ match($domain->ssl_status) {
                                    'issued' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                    'processing', 'requested' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                    default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                } }}">
                                    {{ $domain->ssl_status ?? 'not_requested' }}
                                </span>
                                @if ($domain->ssl_last_error)
                                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $domain->ssl_last_error }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">
                                {{ $domain->ssl_expires_at?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="px-3 py-2">
                                @can('tenant.custom-domains.manage')
                                    <div class="flex flex-wrap gap-2">
                                        <flux:button size="xs" variant="ghost" wire:click="toggleVerification({{ $domain->id }})">
                                            {{ $domain->verified_at ? 'Marcar pendiente' : 'Marcar verificado' }}
                                        </flux:button>

                                        <flux:button size="xs" variant="primary" wire:click="requestSsl({{ $domain->id }})" @disabled(! $domain->verified_at)>
                                            Solicitar SSL
                                        </flux:button>

                                        <flux:button size="xs" variant="danger" wire:click="remove({{ $domain->id }})">
                                            Eliminar
                                        </flux:button>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-3 py-6 text-center text-zinc-500" colspan="5">No hay dominios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>
</div>
