<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:navbar.item>
            <flux:navbar.item icon="building-office-2" :href="route('central.tenants.index')"
                :current="request()->routeIs('central.tenants.*')" wire:navigate>
                {{ __('Tenants') }}
            </flux:navbar.item>
            <flux:navbar.item icon="credit-card" :href="route('central.billing.subscriptions')"
                :current="request()->routeIs('central.billing.*')" wire:navigate>{{ __('Billing') }}</flux:navbar.item>
            <flux:navbar.item icon="megaphone" :href="route('central.affiliates.partners')"
                :current="request()->routeIs('central.affiliates.*')" wire:navigate>
                {{ __('Affiliates') }}
            </flux:navbar.item>
            @can('viewAny', \App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint::class)
                <flux:navbar.item icon="signal" :href="route('central.partners.webhooks.endpoints')"
                    :current="request()->routeIs('central.partners.webhooks.*')" wire:navigate>
                    {{ __('Webhooks') }}
                </flux:navbar.item>
            @endcan
            @can('admin-roles.viewAny')
                <flux:navbar.item icon="shield-check" :href="route('central.admins.roles.index')"
                    :current="request()->routeIs('central.admins.roles.*')" wire:navigate>
                    {{ __('Admin Roles') }}
                </flux:navbar.item>
            @endcan
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
            <flux:tooltip :content="__('Search')" position="bottom">
                <flux:navbar.item class="h-10! [&>div>svg]:size-5" icon="magnifying-glass" href="#"
                    :label="__('Search')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Repository')" position="bottom">
                <flux:navbar.item class="h-10 max-lg:hidden [&>div>svg]:size-5" icon="folder-git-2"
                    href="https://github.com/laravel/livewire-starter-kit" target="_blank" :label="__('Repository')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Documentation')" position="bottom">
                <flux:navbar.item class="h-10 max-lg:hidden [&>div>svg]:size-5" icon="book-open-text"
                    href="https://laravel.com/docs/starter-kits#livewire" target="_blank"
                    :label="__('Documentation')" />
            </flux:tooltip>
        </flux:navbar>

        <x-desktop-user-menu />
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar collapsible="mobile" sticky
        class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')">
                <flux:sidebar.item icon="layout-grid" :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="building-office-2" :href="route('central.tenants.index')"
                    :current="request()->routeIs('central.tenants.*')" wire:navigate>
                    {{ __('Tenants') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="credit-card" :href="route('central.billing.subscriptions')"
                    :current="request()->routeIs('central.billing.subscriptions')" wire:navigate>
                    {{ __('Billing') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="megaphone" :href="route('central.affiliates.partners')"
                    :current="request()->routeIs('central.affiliates.partners')" wire:navigate>
                    {{ __('Affiliates') }}
                </flux:sidebar.item>
                @can('viewAny', \App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint::class)
                    <flux:sidebar.item icon="signal" :href="route('central.partners.webhooks.endpoints')"
                        :current="request()->routeIs('central.partners.webhooks.endpoints')" wire:navigate>
                        {{ __('Endpoints') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="bars-arrow-up" :href="route('central.partners.webhooks.deliveries')"
                        :current="request()->routeIs('central.partners.webhooks.deliveries')" wire:navigate>
                        {{ __('Deliveries') }}
                    </flux:sidebar.item>
                @endcan
                @can('admin-roles.viewAny')
                    <flux:sidebar.item icon="shield-check" :href="route('central.admins.roles.index')"
                        :current="request()->routeIs('central.admins.roles.*')" wire:navigate>
                        {{ __('Admin Roles') }}
                    </flux:sidebar.item>
                @endcan
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>
    </flux:sidebar>

    {{ $slot }}

    @fluxScripts
</body>

</html>
