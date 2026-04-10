<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <flux:icon name="lock-closed" class="mx-auto mb-3 size-10 text-zinc-500" />
            <flux:heading size="xl">Acceso Tenant</flux:heading>
            <flux:subheading>Inicia sesión con tu cuenta aislada para este tenant</flux:subheading>
        </div>

        <flux:card class="p-6">
            <form wire:submit="login" class="space-y-5">
                <flux:field>
                    <flux:label for="tenant-login-email">Email</flux:label>
                    <flux:input id="tenant-login-email" wire:model="form.email" type="email" autocomplete="email"
                        placeholder="tu@empresa.com" required />
                    <flux:error name="form.email" />
                </flux:field>

                <flux:field>
                    <flux:label for="tenant-login-password">Contraseña</flux:label>
                    <flux:input id="tenant-login-password" wire:model="form.password" type="password"
                        autocomplete="current-password" placeholder="••••••••" required />
                    <flux:error name="form.password" />
                </flux:field>

                <flux:checkbox wire:model="form.remember" label="Mantener sesión iniciada" />

                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Acceder</span>
                    <span wire:loading>Verificando...</span>
                </flux:button>
            </form>

            <div class="mt-5 text-center text-sm text-zinc-500 dark:text-zinc-400">
                ¿No tienes cuenta?
                <a href="/register" wire:navigate
                    class="font-medium text-zinc-900 underline dark:text-white">
                    Regístrate aquí
                </a>
            </div>
        </flux:card>
    </div>
</div>
