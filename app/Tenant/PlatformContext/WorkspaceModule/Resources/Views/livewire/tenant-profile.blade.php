<div class="space-y-8 max-w-2xl">

    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl">{{ __('Mi Perfil') }}</flux:heading>
        <flux:subheading>{{ __('Actualiza tu información personal y contraseña.') }}</flux:subheading>
    </div>

    {{-- Sección: Información del perfil --}}
    <flux:card class="p-6 space-y-5">
        <div>
            <flux:heading size="lg">{{ __('Información personal') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mt-1">
                {{ __('Nombre y correo electrónico de tu cuenta en este workspace.') }}
            </flux:text>
        </div>

        @if ($profileSuccess)
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ $profileSuccess }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form wire:submit="updateProfile" class="space-y-4">
            <flux:field>
                <flux:label for="profile-name">{{ __('Nombre') }}</flux:label>
                <flux:input id="profile-name" wire:model="updateProfileForm.name" type="text" autocomplete="name"
                    placeholder="Tu nombre completo" />
                <flux:error name="updateProfileForm.name" />
            </flux:field>

            <flux:field>
                <flux:label for="profile-email">{{ __('Correo electrónico') }}</flux:label>
                <flux:input id="profile-email" wire:model="updateProfileForm.email" type="email" autocomplete="email"
                    placeholder="correo@ejemplo.com" />
                <flux:error name="updateProfileForm.email" />
                @if (!$currentUser->hasVerifiedEmail())
                    <flux:text class="text-xs text-amber-600 mt-1">
                        {{ __('Tu correo no está verificado. Al cambiarlo deberás verificarlo nuevamente.') }}
                    </flux:text>
                @endif
            </flux:field>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    {{ __('Guardar cambios') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

    {{-- Separador --}}
    <flux:separator />

    {{-- Sección: 2FA opcional --}}
    <flux:card class="p-6 space-y-5">
        <div>
            <flux:heading size="lg">{{ __('Autenticación de dos factores (2FA)') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mt-1">
                {{ __('Opcional y recomendada: puedes activarla cuando lo desees para reforzar la seguridad de tu cuenta.') }}
            </flux:text>
        </div>

        @if ($twoFactorSuccess)
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ $twoFactorSuccess }}</flux:callout.heading>
            </flux:callout>
        @endif

        @if (!$currentUser->two_factor_secret)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950">
                <p class="text-sm text-amber-800 dark:text-amber-300">
                    {{ __('2FA no está activado. Es sugerible habilitarlo para reducir riesgos de acceso no autorizado.') }}
                </p>
            </div>

            <div class="flex justify-end">
                <flux:button type="button" variant="primary" wire:click="enableTwoFactor">
                    {{ __('Activar 2FA') }}
                </flux:button>
            </div>
        @else
            <div class="space-y-4">
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                        {{ __('Escanea este QR con tu app autenticadora y confirma con un código de 6 dígitos.') }}
                    </flux:text>
                    <div class="mt-3 rounded bg-white p-4 dark:bg-zinc-900">{!! $currentUser->twoFactorQrCodeSvg() !!}</div>
                </div>

                @if (!$currentUser->hasEnabledTwoFactorAuthentication())
                    <form wire:submit="confirmTwoFactor" class="space-y-4">
                        <flux:field>
                            <flux:label for="two-factor-code">{{ __('Código de confirmación') }}</flux:label>
                            <flux:input id="two-factor-code" wire:model="twoFactorForm.code" type="text"
                                maxlength="6" placeholder="123456" />
                            <flux:error name="twoFactorForm.code" />
                        </flux:field>

                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary">{{ __('Confirmar 2FA') }}</flux:button>
                        </div>
                    </form>
                @endif

                @if ($currentUser->hasEnabledTwoFactorAuthentication())
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('Códigos de recuperación') }}</flux:heading>
                        <div class="mt-3 grid gap-2 md:grid-cols-2">
                            @foreach ($currentUser->recoveryCodes() as $code)
                                <p class="rounded bg-zinc-100 px-2 py-1 font-mono text-xs dark:bg-zinc-800">
                                    {{ $code }}</p>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <flux:button type="button" variant="ghost" wire:click="regenerateRecoveryCodes">
                            {{ __('Regenerar códigos') }}
                        </flux:button>
                        <flux:button type="button" variant="danger" wire:click="disableTwoFactor">
                            {{ __('Desactivar 2FA') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        @endif
    </flux:card>

    {{-- Separador --}}
    <flux:separator />

    {{-- Sección: Cambio de contraseña --}}
    <flux:card class="p-6 space-y-5">
        <div>
            <flux:heading size="lg">{{ __('Cambiar contraseña') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mt-1">
                {{ __('Usa una contraseña larga y aleatoria para mantener tu cuenta segura.') }}
            </flux:text>
        </div>

        @if ($passwordSuccess)
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ $passwordSuccess }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form wire:submit="updatePassword" class="space-y-4">
            <flux:field>
                <flux:label for="current-password">{{ __('Contraseña actual') }}</flux:label>
                <flux:input id="current-password" wire:model="updatePasswordForm.current_password" type="password"
                    autocomplete="current-password" />
                <flux:error name="updatePasswordForm.current_password" />
            </flux:field>

            <flux:field>
                <flux:label for="new-password">{{ __('Nueva contraseña') }}</flux:label>
                <flux:input id="new-password" wire:model="updatePasswordForm.password" type="password"
                    autocomplete="new-password" />
                <flux:error name="updatePasswordForm.password" />
            </flux:field>

            <flux:field>
                <flux:label for="confirm-password">{{ __('Confirmar nueva contraseña') }}</flux:label>
                <flux:input id="confirm-password" wire:model="updatePasswordForm.password_confirmation" type="password"
                    autocomplete="new-password" />
                <flux:error name="updatePasswordForm.password_confirmation" />
            </flux:field>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    {{ __('Actualizar contraseña') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

</div>
