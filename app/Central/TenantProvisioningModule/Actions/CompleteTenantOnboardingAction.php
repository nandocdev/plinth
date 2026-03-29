<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\BillingModule\Actions\CreateSubscriptionAction;
use App\Central\BillingModule\DTOs\CreateSubscriptionData;
use App\Central\BillingModule\Models\Plan;
use App\Central\TenantProvisioningModule\DTOs\CompleteTenantOnboardingData;
use App\Central\TenantProvisioningModule\DTOs\CreateTenantData;
use App\Central\TenantProvisioningModule\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class CompleteTenantOnboardingAction {
   public function __construct(
      private readonly CreateTenantAction $createTenant,
      private readonly CreateSubscriptionAction $createSubscription,
   ) {
   }

   public function execute(CompleteTenantOnboardingData $data): Tenant {
      /** @var Tenant $tenant */
      $tenant = DB::connection('central')->transaction(function () use ($data): Tenant {
         /** @var Plan $plan */
         $plan = Plan::query()->findOrFail($data->planId);

         $tenant = $this->createTenant->execute(
            CreateTenantData::fromValues($data->name, $data->primaryDomain, $data->region),
         );

         $trialEndsAt = $plan->trial_days > 0
            ? CarbonImmutable::now()->addDays($plan->trial_days)->toDateTimeString()
            : null;

         $status = $trialEndsAt === null ? 'active' : 'trialing';

         $this->createSubscription->execute(new CreateSubscriptionData(
            $tenant->id,
            $plan->id,
            $data->billingPeriod,
            $status,
            $trialEndsAt,
         ));

         return $tenant;
      });

      return $tenant;
   }
}
