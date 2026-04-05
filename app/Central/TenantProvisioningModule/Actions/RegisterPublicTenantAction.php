<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\AuthenticationModule\Actions\FindOrCreateSystemAdminAction;
use App\Central\TenantProvisioningModule\DTOs\CompleteTenantOnboardingData;
use App\Central\TenantProvisioningModule\DTOs\PublicTenantRegistrationData;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\AuthenticationModule\Actions\RegisterTenantUserAction;
use App\Tenant\AuthenticationModule\DTOs\RegisterTenantUserData;
use App\Tenant\UserManagementModule\Actions\SeedDefaultRolesAction;
use App\Tenant\UserManagementModule\Enums\TenantRole;

final class RegisterPublicTenantAction {
   public function __construct(
      private readonly CompleteTenantOnboardingAction $completeTenantOnboarding,
      private readonly FindOrCreateSystemAdminAction $findOrCreateSystemAdmin,
      private readonly RegisterTenantUserAction $registerTenantUser,
      private readonly SeedDefaultRolesAction $seedDefaultRoles,
   ) {
   }

   public function execute(PublicTenantRegistrationData $data): Tenant {
      $owner = $this->findOrCreateSystemAdmin->execute(
         $data->adminName,
         $data->adminEmail,
         $data->adminPassword,
      );

      $baseHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
      $primaryDomain = "{$data->subdomain}.{$baseHost}";

      $tenant = $this->completeTenantOnboarding->execute(new CompleteTenantOnboardingData(
         name: $data->companyName,
         primaryDomain: $primaryDomain,
         planId: $data->planId,
         billingPeriod: 'monthly',
         brandName: $data->companyName,
      ));

      $tenant->update([
         'owner_system_admin_id' => $owner->getKey(),
         'owner_email' => $owner->email,
      ]);

      tenancy()->initialize($tenant);

      try {
         $tenantOwner = $this->registerTenantUser->execute(new RegisterTenantUserData(
            name: $data->adminName,
            email: $data->adminEmail,
            password: $data->adminPassword,
         ));

         $this->seedDefaultRoles->execute();
         $tenantOwner->assignRole(TenantRole::Admin->value);
      } finally {
         tenancy()->end();
      }

      return $tenant;
   }
}
