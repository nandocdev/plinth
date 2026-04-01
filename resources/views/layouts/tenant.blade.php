<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 px-4 py-3">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <flux:icon name="building-storefront" class="size-6 text-zinc-600 dark:text-zinc-400" />
                        <span class="font-semibold text-zinc-800 dark:text-zinc-100 text-sm">
                            {{ tenant()?->brandName() ?? config('app.name') }}
                        </span>
                        <flux:badge color="zinc" size="sm">Workspace Tenant</flux:badge>
                    </div>

                    @auth('tenant')
                        <nav class="hidden items-center gap-2 md:flex">
                            <a href="/dashboard" wire:navigate>
                                <flux:button variant="ghost" size="sm" icon="home">Dashboard</flux:button>
                            </a>
                            <a href="/settings/tenant" wire:navigate>
                                <flux:button variant="ghost" size="sm" icon="cog-6-tooth">Configuración</flux:button>
                            </a>
                            <a href="/activity-log" wire:navigate>
                                <flux:button variant="ghost" size="sm" icon="clipboard-document-list">Activity log
                                </flux:button>
                            </a>
                            <a href="/files" wire:navigate>
                                <flux:button variant="ghost" size="sm" icon="paper-clip">Archivos</flux:button>
                            </a>
                            <a href="/notifications" wire:navigate>
                                <flux:button variant="ghost" size="sm" icon="bell">Notificaciones</flux:button>
                            </a>
                            <a href="/billing" wire:navigate>
                                <flux:button variant="ghost" size="sm" icon="credit-card">Facturación</flux:button>
                            </a>
                            <a href="/plan-features" wire:navigate>
                                <flux:button variant="ghost" size="sm" icon="sparkles">Mi Plan</flux:button>
                            </a>
                        </nav>
                    @endauth
                </div>

                @auth('tenant')
                    <flux:button wire:click="logout" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle">
                        Salir
                    </flux:button>
                @endauth
            </div>
        </header>

        <main class="flex-1 py-8 px-4">
            <div class="max-w-5xl mx-auto">
                {{ $slot }}
            </div>
        </main>

        <footer
            class="border-t border-zinc-200 dark:border-zinc-700 py-4 px-4 text-center text-xs text-zinc-500 dark:text-zinc-400">
            {{ config('app.name') }} &mdash; Workspace tenant aislado
        </footer>
    </div>

    @fluxScripts
</body>

</html>
