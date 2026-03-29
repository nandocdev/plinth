<?php

declare(strict_types=1);

namespace App\Central\PartnerWebhookModule\Providers;

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Central\BillingModule\Events\SubscriptionDeleted;
use App\Central\BillingModule\Events\SubscriptionUpdated;
use App\Central\PartnerWebhookModule\Listeners\QueueSubscriptionCreatedPartnerWebhookListener;
use App\Central\PartnerWebhookModule\Listeners\QueueSubscriptionDeletedPartnerWebhookListener;
use App\Central\PartnerWebhookModule\Listeners\QueueSubscriptionUpdatedPartnerWebhookListener;
use App\Central\PartnerWebhookModule\Listeners\QueueTenantCreatedPartnerWebhookListener;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookDelivery;
use App\Central\PartnerWebhookModule\Models\PartnerWebhookEndpoint;
use App\Central\PartnerWebhookModule\Policies\PartnerWebhookDeliveryPolicy;
use App\Central\PartnerWebhookModule\Policies\PartnerWebhookEndpointPolicy;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class PartnerWebhookModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(PartnerWebhookEndpoint::class, PartnerWebhookEndpointPolicy::class);
      Gate::policy(PartnerWebhookDelivery::class, PartnerWebhookDeliveryPolicy::class);

      Event::listen(TenantCreatedFromCentral::class, QueueTenantCreatedPartnerWebhookListener::class);
      Event::listen(SubscriptionCreated::class, QueueSubscriptionCreatedPartnerWebhookListener::class);
      Event::listen(SubscriptionUpdated::class, QueueSubscriptionUpdatedPartnerWebhookListener::class);
      Event::listen(SubscriptionDeleted::class, QueueSubscriptionDeletedPartnerWebhookListener::class);

      $this->loadRoutes();
      $this->loadViews();
      $this->loadMigrationsFrom(database_path('migrations/central'));
   }

   private function loadRoutes(): void {
      if (app()->routesAreCached()) {
         return;
      }

      Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
   }

   private function loadViews(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'partner-webhook');
   }
}
