<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Providers;

use App\Central\AffiliateModule\Listeners\RegisterReferralConversionOnTenantCreated;
use App\Central\AffiliateModule\Models\ReferralConversion;
use App\Central\AffiliateModule\Models\ReferralPartner;
use App\Central\AffiliateModule\Policies\ReferralConversionPolicy;
use App\Central\AffiliateModule\Policies\ReferralPartnerPolicy;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AffiliateModuleServiceProvider extends ServiceProvider {
   public function register(): void {
      //
   }

   public function boot(): void {
      Gate::policy(ReferralPartner::class, ReferralPartnerPolicy::class);
      Gate::policy(ReferralConversion::class, ReferralConversionPolicy::class);

      Event::listen(TenantCreatedFromCentral::class, RegisterReferralConversionOnTenantCreated::class);

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
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'affiliate');
   }
}
