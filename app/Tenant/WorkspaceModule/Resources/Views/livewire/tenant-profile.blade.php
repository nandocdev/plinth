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
