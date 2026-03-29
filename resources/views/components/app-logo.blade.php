@props([
    'sidebar' => false,
])

@php
    /** @var \App\Central\TenantProvisioningModule\Models\Tenant|null $tenant */
    $tenant = null;
    $brandName = config('app.name', 'Laravel Starter Kit');
    $logoUrl = null;
    $primaryColor = '#f53003';

    if (function_exists('tenancy') && tenancy()->initialized) {
        $tenant = tenancy()->tenant;

        if ($tenant instanceof \App\Central\TenantProvisioningModule\Models\Tenant) {
            $brandName = $tenant->brandName();
            $logoUrl = $tenant->logoUrl();
            $primaryColor = $tenant->primaryColor();
        }
    }
@endphp

@if ($sidebar)
    <flux:sidebar.brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md text-white"
            style="background-color: {{ $primaryColor }};">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="size-5 object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md text-white"
            style="background-color: {{ $primaryColor }};">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="size-5 object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white" />
            @endif
        </x-slot>
    </flux:brand>
@endif
