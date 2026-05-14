<div class="mx-auto flex w-full max-w-3xl flex-col gap-6">

    @if ($successRedirectUrl)
        <div class="space-y-4 rounded-2xl border border-green-500/30 bg-green-500/10 p-8 text-center">
            <div class="flex justify-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-500/20">
                    <flux:icon name="check-circle" class="size-8 text-green-400" />
                </div>
            </div>
            <flux:heading size="xl" class="!text-white">¡Workspace creado!</flux:heading>
            <flux:text class="text-zinc-400">Tu onboarding finalizó correctamente. Ya puedes entrar a tu workspace.
            </flux:text>
            <a href="{{ $successRedirectUrl }}" class="inline-block mt-2">
                <flux:button variant="primary" color="indigo" class="!w-full !border-none">
                    Ir a mi workspace
                </flux:button>
            </a>
            <p class="break-all text-xs text-zinc-500">{{ $successRedirectUrl }}</p>
        </div>
    @else
        <div class="text-center">
            <flux:heading size="xl">Registro de Workspace</flux:heading>
            <flux:subheading class="mt-1">Completa los pasos del wizard para crear tu entorno en minutos.
            </flux:subheading>
        </div>

        @include('tenant-provisioning::components.public-signup-steps', [
            'steps' => $steps,
            'currentStep' => $currentStep,
            'progressPercent' => $progressPercent,
        ])

        @error('form.companyName')
            @if (str_contains($message, 'intentos'))
                <flux:callout variant="danger" icon="exclamation-triangle">{{ $message }}</flux:callout>
            @endif
        @enderror

        @error('register')
            <flux:callout variant="danger" icon="x-circle">{{ $message }}</flux:callout>
        @enderror

        <form wire:submit="register" class="space-y-5">
            <div @class(['hidden' => $currentStep !== 1]) wire:key="signup-step-1">
                <flux:card class="space-y-4 p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Paso 1 · Tu empresa</p>

                    <flux:input wire:model.live.debounce.250ms="form.companyName"
                        wire:blur="syncSubdomainFromCompanyName" label="Nombre de la empresa" placeholder="Acme Inc."
                        required />

                    <flux:input.group label="Subdominio">
                        <flux:input wire:model.live.debounce.250ms="form.subdomain" placeholder="acme" required />
                        <flux:input.group.suffix class="text-zinc-500 dark:text-zinc-400">.{{ $baseHost }}
                        </flux:input.group.suffix>
                    </flux:input.group>
                </flux:card>
            </div>

            <div @class(['hidden' => $currentStep !== 2]) wire:key="signup-step-2">
                <flux:card class="space-y-3 p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Paso 2 · Selecciona un plan
                    </p>

                    <div class="grid gap-3">
                        @foreach ($plans as $plan)
                            <label
                                class="flex cursor-pointer items-center gap-4 rounded-xl border p-4 transition-colors
                                @if ($form->planId === $plan->id) border-orange-500 bg-orange-500/5 @else border-zinc-700 hover:border-zinc-500 @endif">
                                <input type="radio" wire:model="form.planId" value="{{ $plan->id }}"
                                    class="accent-orange-500" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-white">{{ $plan->name }}</span>
                                        @if ($plan->trial_days > 0)
                                            <span
                                                class="rounded-full bg-orange-500/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-400">
                                                {{ $plan->trial_days }}d trial
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs text-zinc-400">
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
                </flux:card>
            </div>

            <div @class(['hidden' => $currentStep !== 3]) wire:key="signup-step-3">
                <flux:card class="space-y-4 p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Paso 3 · Cuenta
                        administradora</p>

                    <flux:input wire:model="form.adminName" label="Nombre completo" placeholder="Admin Tenant"
                        required />
                    <flux:input wire:model="form.adminEmail" type="email" label="Correo electrónico"
                        placeholder="user@mail.com" required />
                    <flux:input wire:model="form.adminPassword" type="password" label="Contraseña"
                        placeholder="Mínimo 8 caracteres" required />
                    <flux:input wire:model="form.adminPasswordConfirmation" type="password" label="Confirmar contraseña"
                        required />
                </flux:card>
            </div>

            <div @class(['hidden' => $currentStep !== 4]) wire:key="signup-step-4">
                <flux:card class="space-y-4 p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Paso 4 · Confirmación</p>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-zinc-700 p-3">
                            <p class="text-xs uppercase tracking-widest text-zinc-500">Empresa</p>
                            <p class="text-sm text-zinc-200">{{ $form->companyName ?: '—' }}</p>
                            <p class="mt-1 text-xs text-zinc-400">
                                {{ $form->subdomain ?: 'tuempresa' }}.{{ $baseHost }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-700 p-3">
                            <p class="text-xs uppercase tracking-widest text-zinc-500">Administrador</p>
                            <p class="text-sm text-zinc-200">{{ $form->adminName ?: '—' }}</p>
                            <p class="mt-1 text-xs text-zinc-400">{{ $form->adminEmail ?: '—' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="terms" wire:model="form.terms" class="mt-1 accent-orange-500" />
                        <label for="terms" class="text-sm text-zinc-400">
                            Acepto los <a href="#" class="text-orange-500 hover:underline">Términos de
                                Servicio</a>
                            y la <a href="#" class="text-orange-500 hover:underline">Política de Privacidad</a>.
                        </label>
                    </div>
                    @error('form.terms')
                        <p class="-mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </flux:card>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    @if ($currentStep > 1)
                        <flux:button type="button" wire:click="previousStep" variant="ghost" icon="arrow-left">
                            Anterior
                        </flux:button>
                    @endif
                </div>

                <div class="flex gap-2">
                    @if ($currentStep < $totalSteps)
                        <flux:button type="button" wire:click="nextStep" variant="primary" color="indigo"
                            icon-trailing="arrow-right">
                            Siguiente
                        </flux:button>
                    @else
                        <flux:button type="submit" variant="primary" color="indigo"
                            class="!border-none" wire:loading.attr="disabled">
                            <span wire:loading.remove>Crear workspace</span>
                            <span wire:loading>Creando workspace…</span>
                        </flux:button>
                    @endif
                </div>
            </div>

            <p class="text-center text-sm text-zinc-500">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="text-orange-500 hover:underline">Inicia sesión</a>
            </p>
        </form>
    @endif
</div>
m>
    @endif
</div>
