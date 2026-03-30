<div class="flex flex-col gap-6 w-full max-w-lg mx-auto">

    @if ($successRedirectUrl)
        {{-- Estado de éxito --}}
        <div class="rounded-2xl border border-green-500/30 bg-green-500/10 p-8 text-center space-y-4">
            <div class="flex justify-center">
                <div class="w-14 h-14 rounded-full bg-green-500/20 flex items-center justify-center">
                    <flux:icon name="check-circle" class="size-8 text-green-400" />
                </div>
            </div>
            <flux:heading size="xl" class="!text-white">¡Workspace creado!</flux:heading>
            <flux:text class="text-zinc-400">
                Tu workspace está listo. Ingresa con tus credenciales en tu subdominio personal.
            </flux:text>
            <a href="{{ $successRedirectUrl }}" class="inline-block mt-2">
                <flux:button variant="primary" color="orange" class="!hover:bg-orange-700 !border-none w-full">
                    Ir a mi workspace →
                </flux:button>
            </a>
            <p class="text-xs text-zinc-500 break-all">{{ $successRedirectUrl }}</p>
        </div>
    @else
        {{-- Encabezado --}}
        <div class="text-center">
            <flux:heading size="xl">Crea tu workspace</flux:heading>
            <flux:subheading class="mt-1">
                Configura tu empresa y elige el plan que mejor se adapte.
            </flux:subheading>
        </div>

        {{-- Error de rate limit --}}
        @error('form.companyName')
            @if (str_contains($message, 'intentos'))
                <flux:callout variant="danger" icon="exclamation-triangle">{{ $message }}</flux:callout>
            @endif
        @enderror

        <form wire:submit="register" class="space-y-5">

            {{-- Empresa --}}
            <div class="space-y-4 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Tu empresa</p>

                <flux:input wire:model="form.companyName" label="Nombre de la empresa" placeholder="Acme Inc."
                    required />

                <div>
                    <flux:input wire:model="form.subdomain" wire:model.live.debounce.500ms="form.subdomain"
                        label="Subdominio" placeholder="acme" required>
                        <x-slot name="description">
                            <span class="text-xs text-zinc-500">
                                Tu workspace estará en:
                                <strong
                                    class="text-zinc-300">{{ $form->subdomain ?: 'tuempresa' }}.{{ $baseHost }}</strong>
                            </span>
                        </x-slot>
                    </flux:input>
                </div>
            </div>

            {{-- Plan --}}
            <div class="space-y-3 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Plan</p>

                <div class="grid gap-3">
                    @foreach ($plans as $plan)
                        <label
                            class="flex items-center gap-4 rounded-xl border p-4 cursor-pointer transition-colors
                            @if ($form->planId === $plan->id) border-orange-500 bg-orange-500/5 @else border-zinc-700 hover:border-zinc-500 @endif">
                            <input type="radio" wire:model="form.planId" value="{{ $plan->id }}"
                                class="accent-orange-500" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-white">{{ $plan->name }}</span>
                                    @if ($plan->trial_days > 0)
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-wide bg-orange-500/20 text-orange-400 px-2 py-0.5 rounded-full">{{ $plan->trial_days }}d
                                            trial</span>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-400 mt-0.5">
                                    @if ($plan->price_monthly_cents === 0)
                                        Gratis para siempre
                                    @else
                                        ${{ number_format($plan->price_monthly_cents / 100, 0) }}/mes
                                    @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('form.planId')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Admin --}}
            <div class="space-y-4 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Tu cuenta de administrador</p>

                <flux:input wire:model="form.adminName" label="Nombre completo" placeholder="Fernando Castillo"
                    required />

                <flux:input wire:model="form.adminEmail" type="email" label="Correo electrónico"
                    placeholder="tu@empresa.com" required />

                <flux:input wire:model="form.adminPassword" type="password" label="Contraseña"
                    placeholder="Mínimo 8 caracteres" required />

                <flux:input wire:model="form.adminPasswordConfirmation" type="password" label="Confirmar contraseña"
                    required />
            </div>

            {{-- Términos --}}
            <div class="flex items-start gap-3">
                <input type="checkbox" id="terms" wire:model="form.terms" class="mt-1 accent-orange-500" />
                <label for="terms" class="text-sm text-zinc-400">
                    Acepto los <a href="#" class="text-orange-500 hover:underline">Términos de Servicio</a>
                    y la <a href="#" class="text-orange-500 hover:underline">Política de Privacidad</a>.
                </label>
            </div>
            @error('form.terms')
                <p class="text-xs text-red-400 -mt-3">{{ $message }}</p>
            @enderror

            <flux:button type="submit" variant="primary" color="orange"
                class="!w-full !hover:bg-orange-700 !border-none" wire:loading.attr="disabled">
                <span wire:loading.remove>Crear workspace gratis</span>
                <span wire:loading>Creando workspace…</span>
            </flux:button>

            <p class="text-center text-sm text-zinc-500">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="text-orange-500 hover:underline">Inicia sesión</a>
            </p>
        </form>
    @endif
</div>
