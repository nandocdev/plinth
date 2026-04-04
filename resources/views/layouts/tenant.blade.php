<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <script>
        (function() {
            const key = 'tenant_theme_preference';
            const saved = localStorage.getItem(key) || 'system';
            const root = document.documentElement;

            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = saved === 'dark' || (saved === 'system' && prefersDark);

            root.classList.toggle('dark', isDark);
            root.dataset.tenantTheme = saved;
        })();
    </script>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-100 text-zinc-900 transition-colors dark:bg-zinc-900 dark:text-zinc-100"
    x-data="{
        theme: document.documentElement.dataset.tenantTheme || 'system',
        init() {
            this.applyTheme();
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const callback = () => {
                if (this.theme === 'system') {
                    this.applyTheme();
                }
            };
            if (media.addEventListener) {
                media.addEventListener('change', callback);
            } else {
                media.addListener(callback);
            }
        },
        cycleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : (this.theme === 'dark' ? 'system' : 'light');
            localStorage.setItem('tenant_theme_preference', this.theme);
            this.applyTheme();
        },
        applyTheme() {
            const mediaDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = this.theme === 'dark' || (this.theme === 'system' && mediaDark);
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.dataset.tenantTheme = this.theme;
        },
        themeLabel() {
            return this.theme === 'light' ? 'Claro' : (this.theme === 'dark' ? 'Oscuro' : 'Sistema');
        },
        themeIcon() {
            return this.theme === 'light' ? 'sun' : (this.theme === 'dark' ? 'moon' : 'computer-desktop');
        }
    }">
    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(245,48,3,0.12),_transparent_38%),linear-gradient(to_bottom,_#fafafa_0%,_#f4f4f5_100%)] dark:bg-[radial-gradient(circle_at_top,_rgba(245,48,3,0.10),_transparent_35%),linear-gradient(to_bottom,_#0a0a0b_0%,_#111114_100%)]">
        <div class="mx-auto flex min-h-screen max-w-6xl flex-col">
            <header
                class="sticky top-0 z-20 border-b border-zinc-200/90 bg-zinc-50/90 px-4 py-3 backdrop-blur dark:border-zinc-700/80 dark:bg-zinc-900/80">
                <div class="mx-auto flex max-w-6xl items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3">
                            <flux:icon name="building-storefront" class="size-6 text-zinc-600 dark:text-zinc-400" />
                            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
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
                                    <flux:button variant="ghost" size="sm" icon="cog-6-tooth">Configuración
                                    </flux:button>
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

                    <div class="flex items-center gap-2">
                        <flux:button type="button" variant="ghost" size="sm" icon="adjustments-horizontal"
                            x-on:click="cycleTheme()">
                            Tema:
                            <span x-text="themeLabel()">Sistema</span>
                        </flux:button>

                        @auth('tenant')
                            <flux:button wire:click="logout" variant="ghost" size="sm"
                                icon="arrow-right-start-on-rectangle">
                                Salir
                            </flux:button>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-8">
                <div class="mx-auto max-w-6xl">
                    <div
                        class="rounded-2xl border border-zinc-200/80 bg-white/85 p-5 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/55 sm:p-6">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <footer
                class="border-t border-zinc-200/80 px-4 py-4 text-center text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                {{ config('app.name') }} &mdash; Workspace tenant aislado
            </footer>
        </div>
    </div>

    @fluxScripts
</body>

</html>
