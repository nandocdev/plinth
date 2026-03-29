<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Providers;

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Tenant\SelfServiceBillingModule\Events\PlanUpgradeRequestedByTenant;
use App\Tenant\SelfServiceBillingModule\Listeners\CreateInvoiceOnPlanUpgradeListener;
use App\Tenant\SelfServiceBillingModule\Listeners\CreateInvoiceOnSubscriptionCreatedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class SelfServiceBillingModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Event::listen(SubscriptionCreated::class, CreateInvoiceOnSubscriptionCreatedListener::class);
      Event::listen(PlanUpgradeRequestedByTenant::class, CreateInvoiceOnPlanUpgradeListener::class);

      $this->loadViews();

      // Las rutas se inyectan en routes/tenant.php vía require desde el grupo de dominio.
   }

   private function loadViews(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'self-service');
   }
}
