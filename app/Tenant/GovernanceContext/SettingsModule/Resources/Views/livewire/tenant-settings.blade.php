<section class="space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Configuración del tenant</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            Actualiza información de empresa, branding y preferencias del workspace.
        </p>
    </div>

    @if ($successMessage)
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div
            class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Empresa</h2>

            <flux:input wire:model="form.companyName" label="Nombre comercial" placeholder="Acme Inc." />
            <flux:input wire:model="form.legalName" label="Razón social" placeholder="Acme Incorporated LLC" />
            <flux:input wire:model="form.supportEmail" label="Email de soporte" placeholder="soporte@acme.com" />
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Branding</h2>

            <flux:input wire:model="form.brandName" label="Nombre de marca" placeholder="Acme Workspace" />
            <flux:input wire:model="form.logoUrl" label="Logo URL" placeholder="https://cdn.example.com/logo.svg" />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="form.primaryColor" label="Color primario" placeholder="#0f172a" />
                <flux:input wire:model="form.secondaryColor" label="Color secundario" placeholder="#2563eb" />
            </div>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Preferencias</h2>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:select wire:model="form.locale" label="Idioma">
                    @foreach ($localeOptions as $localeValue => $localeLabel)
                        <flux:select.option value="{{ $localeValue }}">{{ $localeLabel }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="form.timezone" label="Zona horaria" placeholder="America/Bogota" />
                <flux:select wire:model="form.currency" label="Moneda">
                    @foreach ($currencyOptions as $currencyValue => $currencyLabel)
                        <flux:select.option value="{{ $currencyValue }}">{{ $currencyLabel }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:select wire:model="form.dateFormat" label="Formato de fecha">
                <flux:select.option value="d/m/Y">DD/MM/YYYY</flux:select.option>
                <flux:select.option value="m/d/Y">MM/DD/YYYY</flux:select.option>
                <flux:select.option value="Y-m-d">YYYY-MM-DD</flux:select.option>
            </flux:select>

            <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                <input type="checkbox" wire:model="form.allowWeeklyDigest"
                    class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500" />
                Recibir resumen semanal de actividad
            </label>
        </div>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Guardar configuración</flux:button>
        </div>
    </form>
</section>
