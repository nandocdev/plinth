<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-lg">
        <div class="mb-8 text-center">
            <flux:icon name="user-plus" class="mx-auto mb-3 size-10 text-zinc-500" />
            <flux:heading size="xl">Registro Tenant</flux:heading>
            <flux:subheading>Crea tu cuenta aislada para este workspace tenant</flux:subheading>
        </div>

        <flux:card class="p-6">
            <form wire:submit="register" class="space-y-5">
                <flux:field>
                    <flux:label for="tenant-register-name">Nombre</flux:label>
                    <flux:input id="tenant-register-name" wire:model="form.name" type="text" autocomplete="name"
                        placeholder="Ada Lovelace" required />
                    <flux:error name="form.name" />
                </flux:field>

                <flux:field>
                    <flux:label for="tenant-register-email">Email</flux:label>
                    <flux:input id="tenant-register-email" wire:model="form.email" type="email" autocomplete="email"
                        placeholder="tu@empresa.com" required />
                    <flux:error name="form.email" />
                </flux:field>

                <div class="grid gap-5 md:grid-cols-2">
                    <flux:field>
                        <flux:label for="tenant-register-password">Contraseña</flux:label>
                        <flux:input id="tenant-register-password" wire:model="form.password" type="password"
                            autocomplete="new-password" placeholder="••••••••" required />
                        <flux:error name="form.password" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="tenant-register-password-confirmation">Confirmar contraseña</flux:label>
                        <flux:input id="tenant-register-password-confirmation" wire:model="form.passwordConfirmation"
                            type="password" autocomplete="new-password" placeholder="••••••••" required />
                        <flux:error name="form.passwordConfirmation" />
                    </flux:field>
                </div>

                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Crear cuenta</span>
                    <span wire:loading>Registrando...</span>
                </flux:button>
            </form>

            <div class="mt-5 text-center text-sm text-zinc-500 dark:text-zinc-400">
                ¿Ya tienes cuenta?
                <a href="/login" wire:navigate
                    class="font-medium text-zinc-900 underline dark:text-white">
                    Inicia sesión
                </a>
            </div>
        </flux:card>
    </div>
</div>
