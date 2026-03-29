@php
    /** @var \App\Central\TenantProvisioningModule\Models\Tenant|null $tenant */
    $tenant = null;
    $tenantBrandName = config('app.name', 'Laravel');
    $tenantPrimaryColor = '#f53003';
    $tenantSecondaryColor = '#ff4433';

    if (function_exists('tenancy') && tenancy()->initialized) {
        $tenant = tenancy()->tenant;

        if ($tenant instanceof \App\Central\TenantProvisioningModule\Models\Tenant) {
            $tenantBrandName = $tenant->brandName();
            $tenantPrimaryColor = $tenant->primaryColor();
            $tenantSecondaryColor = $tenant->secondaryColor();
        }
    }
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title . ' - ' . $tenantBrandName : $tenantBrandName }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    :root {
        --brand-primary: {{ $tenantPrimaryColor }};
        --brand-secondary: {{ $tenantSecondaryColor }};
    }
</style>

@fluxAppearance
