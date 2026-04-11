@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('tenant.dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @auth('tenant')
                @foreach (\App\Shared\Helpers\TenantSidebarMenuHelper::getMenu() as $section)
                    <flux:sidebar.group :heading="$section['heading']" class="grid">
                        @foreach ($section['items'] as $item)
                            @if (empty($item['children']))
                                <flux:sidebar.item :icon="$item['icon']" :href="route($item['route'])"
                                    :current="$item['active']" wire:navigate>
                                    {{ $item['label'] }}
                                </flux:sidebar.item>
                            @else
                                <x-tenant-nav-group :item="$item" />
                            @endif
                        @endforeach
                    </flux:sidebar.group>
                @endforeach
            @endauth
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <div x-data="{
                mode: localStorage.getItem('flux.appearance') || 'system',
                cycle() {
                    const order = ['light', 'dark', 'system'];
                    this.mode = order[(order.indexOf(this.mode) + 1) % order.length];
                    window.Flux?.applyAppearance(this.mode);
                },
                labels: { light: 'Claro', dark: 'Oscuro', system: 'Sistema' }
            }">
                <flux:button type="button" variant="ghost" size="sm" class="w-full justify-start" @click="cycle()">
                    <flux:icon name="sun" class="size-4 shrink-0" x-show="mode === 'light'" x-cloak />
                    <flux:icon name="moon" class="size-4 shrink-0" x-show="mode === 'dark'" x-cloak />
                    <flux:icon name="computer-desktop" class="size-4 shrink-0" x-show="mode === 'system'" x-cloak />
                    Tema:&nbsp;<span x-text="labels[mode]">Sistema</span>
                </flux:button>
            </div>
        </flux:sidebar.nav>

        @auth('tenant')
            <x-tenant-user-menu />
        @endauth
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        @auth('tenant')
            <x-tenant-user-menu variant="mobile" />
        @endauth
    </flux:header>

    <flux:main>
        <div class="mx-auto max-w-6xl">
            <div
                class="rounded-2xl border border-zinc-200/80 bg-white/85 p-5 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/55 sm:p-6">
                {{ $slot }}
            </div>
        </div>
    </flux:main>

    @fluxScripts
</body>

</html>
