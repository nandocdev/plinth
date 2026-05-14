@props([
    'sidebar' => false,
])

@php
    /** @var array<string, mixed>|null $tenantSettings */
    $tenantSettings = $tenantSettings ?? null;

    /** @var \App\Central\TenantProvisioningModule\Models\Tenant|null $tenant */
    $tenant = null;
    $brandName = (string) config('app.name', 'Laravel Starter Kit');
    $logoUrl = null;
    $primaryColor = '#f53003';

    if (is_array($tenantSettings)) {
        $brandName = (string) ($tenantSettings['brandName'] ?? $brandName);

        if ($brandName === '') {
            $brandName = (string) ($tenantSettings['companyName'] ?? $brandName);
        }

        $logoUrl = (string) ($tenantSettings['logoUrl'] ?? '');
        $logoUrl = $logoUrl !== '' ? $logoUrl : null;
        $primaryColor = (string) ($tenantSettings['primaryColor'] ?? $primaryColor);
    }

    if (function_exists('tenancy') && tenancy()->initialized) {
        $tenant = tenancy()->tenant;

        if ($tenant instanceof \App\Central\TenantProvisioningModule\Models\Tenant) {
            if (!is_array($tenantSettings)) {
                $brandName = $tenant->brandName();
                $logoUrl = $tenant->logoUrl();
                $primaryColor = $tenant->primaryColor();
            }
        }
    }
@endphp

@if ($sidebar)
    <flux:sidebar.brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md text-white">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="size-5 object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md text-white">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="size-5 object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white" />
            @endif
        </x-slot>
    </flux:brand>
@endif
