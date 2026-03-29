<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <flux:icon name="credit-card" class="size-10 mx-auto text-zinc-500 mb-3" />
            <flux:heading size="xl">Portal de Facturación</flux:heading>
            <flux:subheading>Accede para gestionar tu suscripción y facturas</flux:subheading>
        </div>

        <flux:card class="p-6">
            <form wire:submit="login" class="space-y-5">
                <flux:field>
                    <flux:label for="email">Email</flux:label>
                    <flux:input id="email" wire:model="form.email" type="email" autocomplete="email"
                        placeholder="tu@empresa.com" required />
                    <flux:error name="form.email" />
                </flux:field>

                <flux:field>
                    <flux:label for="password">Contraseña</flux:label>
                    <flux:input id="password" wire:model="form.password" type="password"
                        autocomplete="current-password" placeholder="••••••••" required />
                    <flux:error name="form.password" />
                </flux:field>

                <flux:checkbox wire:model="form.remember" label="Mantener sesión iniciada" />

                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Acceder</span>
                    <span wire:loading>Verificando...</span>
                </flux:button>
            </form>
        </flux:card>
    </div>
</div>
