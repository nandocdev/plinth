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
        sidebarOpen: false,
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
        <div class="flex min-h-screen">
            <aside
                class="hidden w-72 shrink-0 border-r border-zinc-200/80 bg-zinc-50/80 p-4 backdrop-blur dark:border-zinc-700/70 dark:bg-zinc-900/70 lg:flex lg:flex-col">
                <div class="flex items-center gap-3 border-b border-zinc-200/80 pb-4 dark:border-zinc-700/70">
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-zinc-900 text-white dark:bg-zinc-200 dark:text-zinc-900">
                        <flux:icon name="building-storefront" class="size-5" />
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-zinc-500">Workspace Tenant</p>
                        <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ tenant()?->brandName() ?? config('app.name') }}
                        </p>
                    </div>
                </div>

                @auth('tenant')
                    <nav class="mt-4 space-y-6">
                        @foreach (\App\Shared\Helpers\TenantSidebarMenuHelper::getMenu() as $section)
                            <div>
                                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                    {{ $section['heading'] }}
                                </p>
                                <div class="space-y-1">
                                    @foreach ($section['items'] as $item)
                                        @if (empty($item['children']))
                                            <a href="{{ route($item['route']) }}" wire:navigate
                                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                                <flux:icon :name="$item['icon']" class="size-4" />
                                                <span>{{ $item['label'] }}</span>
                                            </a>
                                        @else
                                            <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }">
                                                <button @click="open = !open"
                                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'text-zinc-900 dark:text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                                    <div class="flex items-center gap-3">
                                                        <flux:icon :name="$item['icon']" class="size-4" />
                                                        <span>{{ $item['label'] }}</span>
                                                    </div>
                                                    <flux:icon name="chevron-down" class="size-3 transition-transform duration-200"
                                                        ::class="{ 'rotate-180': open }" />
                                                </button>
                                                <div x-show="open" x-collapse class="ml-7 mt-1 space-y-1 border-l border-zinc-200 pl-3 dark:border-zinc-700">
                                                    @foreach ($item['children'] as $child)
                                                        <a href="{{ route($child['route']) }}" wire:navigate
                                                            class="block rounded-md px-3 py-1.5 text-xs font-medium transition {{ $child['active'] ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                                                            {{ $child['label'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                @endauth

                <div class="mt-auto space-y-2 border-t border-zinc-200/80 pt-4 dark:border-zinc-700/70">
                    <flux:button type="button" variant="ghost" size="sm" icon="adjustments-horizontal"
                        class="w-full justify-start" x-on:click="cycleTheme()">
                        Tema:
                        <span x-text="themeLabel()">Sistema</span>
                    </flux:button>

                    @auth('tenant')
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <flux:button type="submit" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle"
                                class="w-full justify-start">
                                Salir
                            </flux:button>
                        </form>
                    @endauth
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header
                    class="sticky top-0 z-20 border-b border-zinc-200/90 bg-zinc-50/90 px-4 py-3 backdrop-blur dark:border-zinc-700/80 dark:bg-zinc-900/80">
                    <div class="mx-auto flex w-full max-w-6xl items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white p-2 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 lg:hidden"
                                x-on:click="sidebarOpen = true" aria-label="Abrir menú">
                                <flux:icon name="bars-3" class="size-5" />
                            </button>

                            <div class="flex items-center gap-2">
                                <flux:icon name="building-storefront" class="size-5 text-zinc-600 dark:text-zinc-400" />
                                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ tenant()?->brandName() ?? config('app.name') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 lg:hidden">
                            <flux:button type="button" variant="ghost" size="sm" icon="adjustments-horizontal"
                                x-on:click="cycleTheme()">
                                <span x-text="themeLabel()">Sistema</span>
                            </flux:button>
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

            <div class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" x-show="sidebarOpen"
                x-transition.opacity x-cloak x-on:click="sidebarOpen = false"></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 w-72 border-r border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:hidden"
                x-show="sidebarOpen" x-transition:enter="transform transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-150"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" x-cloak>
                <div class="mb-4 flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-zinc-500">Workspace Tenant</p>
                        <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ tenant()?->brandName() ?? config('app.name') }}
                        </p>
                    </div>
                    <button type="button"
                        class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                        x-on:click="sidebarOpen = false" aria-label="Cerrar menú">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                @auth('tenant')
                    <nav class="space-y-6">
                        @foreach (\App\Shared\Helpers\TenantSidebarMenuHelper::getMenu() as $section)
                            <div>
                                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                    {{ $section['heading'] }}
                                </p>
                                <div class="space-y-1">
                                    @foreach ($section['items'] as $item)
                                        @if (empty($item['children']))
                                            <a href="{{ route($item['route']) }}" wire:navigate x-on:click="sidebarOpen = false"
                                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                                <flux:icon :name="$item['icon']" class="size-4" />
                                                <span>{{ $item['label'] }}</span>
                                            </a>
                                        @else
                                            <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }">
                                                <button @click="open = !open"
                                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'text-zinc-900 dark:text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                                                    <div class="flex items-center gap-3">
                                                        <flux:icon :name="$item['icon']" class="size-4" />
                                                        <span>{{ $item['label'] }}</span>
                                                    </div>
                                                    <flux:icon name="chevron-down" class="size-3 transition-transform duration-200"
                                                        ::class="{ 'rotate-180': open }" />
                                                </button>
                                                <div x-show="open" x-collapse class="ml-7 mt-1 space-y-1 border-l border-zinc-200 pl-3 dark:border-zinc-700">
                                                    @foreach ($item['children'] as $child)
                                                        <a href="{{ route($child['route']) }}" wire:navigate x-on:click="sidebarOpen = false"
                                                            class="block rounded-md px-3 py-1.5 text-xs font-medium transition {{ $child['active'] ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                                                            {{ $child['label'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                @endauth

                <div class="mt-4 space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button type="button" variant="ghost" size="sm" icon="adjustments-horizontal"
                        class="w-full justify-start" x-on:click="cycleTheme()">
                        Tema:
                        <span x-text="themeLabel()">Sistema</span>
                    </flux:button>

                    @auth('tenant')
                        <form method="POST" action="{{ url('/logout') }}" x-on:submit="sidebarOpen = false">
                            @csrf
                            <flux:button type="submit" variant="ghost" size="sm"
                                icon="arrow-right-start-on-rectangle" class="w-full justify-start">
                                Salir
                            </flux:button>
                        </form>
                    @endauth
                </div>
            </aside>
        </div>
    </div>

    @fluxScripts
</body>

</html>
