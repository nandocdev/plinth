<?php

return [
    App\Central\AdminAuthorizationModule\Providers\AdminAuthorizationModuleServiceProvider::class,
    App\Central\AffiliateModule\Providers\AffiliateModuleServiceProvider::class,
    App\Central\ActivityLogModule\Providers\ActivityLogModuleServiceProvider::class,
    App\Central\AuthenticationModule\Providers\AuthenticationModuleServiceProvider::class,
    App\Central\AuthenticationModule\Providers\FortifyServiceProvider::class,
    App\Central\BillingModule\Providers\BillingModuleServiceProvider::class,
    App\Central\DataExportModule\Providers\DataExportModuleServiceProvider::class,
    App\Central\NotificationModule\Providers\NotificationModuleServiceProvider::class,
    App\Central\PartnerWebhookModule\Providers\PartnerWebhookModuleServiceProvider::class,
    App\Central\SystemHealthModule\Providers\SystemHealthModuleServiceProvider::class,
    App\Central\TenantProvisioningModule\Providers\TenantProvisioningModuleServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    App\Shared\Infrastructure\Providers\AppServiceProvider::class,

    // Identity Context
    App\Tenant\IdentityContext\AuthenticationModule\Providers\AuthenticationModuleServiceProvider::class,
    App\Tenant\IdentityContext\ApiAccessModule\Providers\ApiAccessModuleServiceProvider::class,
    App\Tenant\IdentityContext\AuthorizationModule\Providers\AuthorizationModuleServiceProvider::class,
    App\Tenant\IdentityContext\ImpersonationModule\Providers\ImpersonationModuleServiceProvider::class,
    App\Tenant\IdentityContext\UserManagementModule\Providers\UserManagementModuleServiceProvider::class,

    // Operations Context
    App\Tenant\OperationsContext\ActivityLogModule\Providers\TenantActivityLogModuleServiceProvider::class,
    App\Tenant\OperationsContext\NotificationModule\Providers\TenantNotificationModuleServiceProvider::class,
    App\Tenant\OperationsContext\QueueModule\Providers\QueueModuleServiceProvider::class,
    App\Tenant\OperationsContext\ReportingModule\Providers\ReportingModuleServiceProvider::class,
    App\Tenant\OperationsContext\WebhookModule\Providers\WebhookModuleServiceProvider::class,

    // Governance Context
    App\Tenant\GovernanceContext\AddonsModule\Providers\AddonsModuleServiceProvider::class,
    App\Tenant\GovernanceContext\CustomDomainModule\Providers\CustomDomainModuleServiceProvider::class,
    App\Tenant\GovernanceContext\FeatureFlagsModule\Providers\FeatureFlagsModuleServiceProvider::class,
    App\Tenant\GovernanceContext\SettingsModule\Providers\SettingsModuleServiceProvider::class,
    App\Tenant\GovernanceContext\SelfServiceBillingModule\Providers\SelfServiceBillingModuleServiceProvider::class,

    // Platform Context
    App\Tenant\PlatformContext\ErrorHandlingModule\Providers\ErrorHandlingModuleServiceProvider::class,
    App\Tenant\PlatformContext\ExportImportModule\Providers\ExportImportModuleServiceProvider::class,
    App\Tenant\PlatformContext\FileUploadModule\Providers\FileUploadModuleServiceProvider::class,
    App\Tenant\PlatformContext\WorkspaceModule\Providers\WorkspaceModuleServiceProvider::class,
];
