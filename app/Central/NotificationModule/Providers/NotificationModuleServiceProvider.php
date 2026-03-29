<?php

declare(strict_types=1);

namespace App\Central\NotificationModule\Providers;

use App\Central\BillingModule\Events\SubscriptionCreated;
use App\Central\BillingModule\Events\SubscriptionDeleted;
use App\Central\BillingModule\Events\SubscriptionUpdated;
use App\Central\NotificationModule\Listeners\SendNewTenantNotificationListener;
use App\Central\NotificationModule\Listeners\SendSubscriptionCreatedNotificationListener;
use App\Central\NotificationModule\Listeners\SendSubscriptionDeletedNotificationListener;
use App\Central\NotificationModule\Listeners\SendSubscriptionUpdatedNotificationListener;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class NotificationModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Event::listen(TenantCreatedFromCentral::class, SendNewTenantNotificationListener::class);
      Event::listen(SubscriptionCreated::class, SendSubscriptionCreatedNotificationListener::class);
      Event::listen(SubscriptionUpdated::class, SendSubscriptionUpdatedNotificationListener::class);
      Event::listen(SubscriptionDeleted::class, SendSubscriptionDeletedNotificationListener::class);
   }
}
