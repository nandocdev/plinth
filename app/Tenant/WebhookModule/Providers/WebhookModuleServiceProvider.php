<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Providers;

use App\Shared\Infrastructure\Support\RegistersTenantRoutes;
use App\Tenant\WebhookModule\Models\IncomingWebhookToken;
use App\Tenant\WebhookModule\Models\WebhookEndpoint;
use App\Tenant\WebhookModule\Policies\IncomingWebhookTokenPolicy;
use App\Tenant\WebhookModule\Policies\WebhookEndpointPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class WebhookModuleServiceProvider extends ServiceProvider {
   use RegistersTenantRoutes;

   public function register(): void {
      //
   }

   public function boot(): void {
      $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'webhook');

      Gate::policy(WebhookEndpoint::class, WebhookEndpointPolicy::class);
      Gate::policy(IncomingWebhookToken::class, IncomingWebhookTokenPolicy::class);

      $this->registerTenantRoutes(__DIR__ . '/../Routes/tenant.php');
   }
}
