<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Providers;

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\SelfServiceBillingModule\Events\PlanUpgradeRequestedByTenant;
use App\Tenant\SelfServiceBillingModule\Listeners\CreateInvoiceOnPlanUpgradeListener;
use App\Tenant\SelfServiceBillingModule\Listeners\CreateInvoiceOnSubscriptionCreatedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class SelfServiceBillingModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      Event::listen(SubscriptionCreated::class, CreateInvoiceOnSubscriptionCreatedListener::class);
      Event::listen(PlanUpgradeRequestedByTenant::class, CreateInvoiceOnPlanUpgradeListener::class);

      $this->loadViews();
      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }

   private function loadViews(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'self-service');
   }
}
