<div class="flex flex-col gap-6 mx-auto max-w-4xl py-8">
    {{-- Header del Wizard --}}
    <header class="flex flex-col gap-2">
        <flux:heading size="xl" level="1">Onboarding de Tenant</flux:heading>
        <flux:subheading>Configura el espacio de trabajo, el dominio y el plan de facturación en un solo paso.</flux:subheading>
    </header>

    @if (session('status'))
        <flux:card class="bg-green-50 dark:bg-green-950/20 border-green-200 dark:border-green-900/50 py-3 px-4">
            <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                <flux:icon name="check-circle" variant="micro" />
                <p class="text-sm font-medium">{{ session('status') }}</p>
            </div>
        </flux:card>
    @endif

    <flux:card>
        <form wire:submit="onboardTenant" class="space-y-8">
            {{-- Sección 1: Identidad --}}
            <section class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <flux:heading size="lg">Identidad del Espacio</flux:heading>
                    <flux:subheading>Nombre comercial y dirección técnica del nuevo tenant.</flux:subheading>
                </div>

                <flux:input wire:model="form.name" label="Nombre de la Organización" placeholder="Ej: Acme Corp" required />
                <flux:input wire:model="form.primaryDomain" label="Subdominio Principal" placeholder="acme" required />
                
                <div class="md:col-span-2 grid gap-6 md:grid-cols-2">
                    <flux:input wire:model="form.brandName" label="Nombre de Marca (Visual)" placeholder="Acme Workspace" />
                    
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Región de Despliegue</label>
                        <select wire:model="form.region" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-orange-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" required>
                            @foreach ($regionOptions as $region)
                                <option value="{{ $region->code }}">{{ $region->label }} ({{ $region->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <flux:separator />

            {{-- Sección 2: Facturación --}}
            <section class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <flux:heading size="lg">Suscripción y Facturación</flux:heading>
                    <flux:subheading>Define el nivel de servicio y el ciclo de cobro inicial.</flux:subheading>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Plan Seleccionado</label>
                    <select wire:model="form.planId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-orange-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" required>
                        <option value="">-- Seleccionar un plan --</option>
                        @foreach ($planOptions as $plan)
                            <option value="{{ $plan['id'] }}">{{ $plan['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Ciclo de Facturación</label>
                    <select wire:model="form.billingPeriod" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-orange-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" required>
                        <option value="monthly">Mensual</option>
                        <option value="yearly">Anual</option>
                    </select>
                </div>

                <flux:input wire:model="form.referralCode" label="Código de Referido / Partner" placeholder="PARTNER2026" />
            </section>

            <flux:separator />

            {{-- Sección 3: Apariencia --}}
            <section class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <flux:heading size="lg">Apariencia Visual</flux:heading>
                    <flux:subheading>Configura el branding inicial para una experiencia personalizada.</flux:subheading>
                </div>

                <flux:input wire:model="form.logoUrl" label="URL del Logotipo" placeholder="https://..." class="md:col-span-2" />
                
                <flux:input wire:model="form.primaryColor" label="Color Primario" type="color" />
                <flux:input wire:model="form.secondaryColor" label="Color Secundario" type="color" />
            </section>

            <footer class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                <flux:button href="{{ route('central.tenants.index') }}" wire:navigate variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary" color="orange" icon="sparkles">
                    Finalizar y Crear Tenant
                </flux:button>
            </footer>
        </form>
    </flux:card>
</div>
