<?php

return [
    App\Central\AdminAuthorizationModule\Providers\AdminAuthorizationModuleServiceProvider::class,
    App\Central\AffiliateModule\Providers\AffiliateModuleServiceProvider::class,
    App\Central\ActivityLogModule\Providers\ActivityLogModuleServiceProvider::class,
    App\Central\AuthenticationModule\Providers\AuthenticationModuleServiceProvider::class,
    App\Central\AuthenticationModule\Providers\FortifyServiceProvider::class,
    App\Central\BillingModule\Providers\BillingModuleServiceProvider::class,
    App\Central\NotificationModule\Providers\NotificationModuleServiceProvider::class,
    App\Central\PartnerWebhookModule\Providers\PartnerWebhookModuleServiceProvider::class,
    App\Central\SystemHealthModule\Providers\SystemHealthModuleServiceProvider::class,
    App\Central\TenantProvisioningModule\Providers\TenantProvisioningModuleServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    App\Shared\Infrastructure\Providers\AppServiceProvider::class,
    App\Tenant\FeatureFlagsModule\Providers\FeatureFlagsModuleServiceProvider::class,
];
