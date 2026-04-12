@php
    /** @var array<string, mixed>|null $tenantSettings */
    $tenantSettings = $tenantSettings ?? null;

    /** @var \App\Central\TenantProvisioningModule\Models\Tenant|null $tenant */
    $tenant = null;
    $tenantBrandName = (string) ($tenantBrandName ?? config('app.name', 'Laravel'));
    $tenantPrimaryColor = (string) ($tenantPrimaryColor ?? '#f53003');
    $tenantSecondaryColor = (string) ($tenantSecondaryColor ?? '#ff4433');

    if (is_array($tenantSettings)) {
        $tenantBrandName = (string) ($tenantSettings['brandName'] ?? $tenantBrandName);

        if ($tenantBrandName === '') {
            $tenantBrandName = (string) ($tenantSettings['companyName'] ?? $tenantBrandName);
        }

        $tenantPrimaryColor = (string) ($tenantSettings['primaryColor'] ?? $tenantPrimaryColor);
        $tenantSecondaryColor = (string) ($tenantSettings['secondaryColor'] ?? $tenantSecondaryColor);
    }

    if (function_exists('tenancy') && tenancy()->initialized) {
        $tenant = tenancy()->tenant;

        if ($tenant instanceof \App\Central\TenantProvisioningModule\Models\Tenant) {
            if (!is_array($tenantSettings)) {
                $tenantBrandName = $tenant->brandName();
                $tenantPrimaryColor = $tenant->primaryColor();
                $tenantSecondaryColor = $tenant->secondaryColor();
            }
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
