<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
   public function up(): void {
      DB::connection('central')->statement("UPDATE tenant_subscriptions SET status = 'canceled' WHERE status NOT IN ('trialing', 'active', 'past_due', 'canceled', 'deleted')");
      DB::connection('central')->statement("UPDATE tenant_subscriptions SET billing_period = 'monthly' WHERE billing_period NOT IN ('monthly', 'yearly')");

      DB::connection('central')->statement('ALTER TABLE tenant_subscriptions DROP CONSTRAINT IF EXISTS tenant_subscriptions_status_check');
      DB::connection('central')->statement('ALTER TABLE tenant_subscriptions DROP CONSTRAINT IF EXISTS tenant_subscriptions_billing_period_check');
      DB::connection('central')->statement('ALTER TABLE tenant_subscriptions DROP CONSTRAINT IF EXISTS tenant_subscriptions_deleted_requires_end_check');

      DB::connection('central')->statement("ALTER TABLE tenant_subscriptions ADD CONSTRAINT tenant_subscriptions_status_check CHECK (status IN ('trialing', 'active', 'past_due', 'canceled', 'deleted'))");
      DB::connection('central')->statement("ALTER TABLE tenant_subscriptions ADD CONSTRAINT tenant_subscriptions_billing_period_check CHECK (billing_period IN ('monthly', 'yearly'))");
      DB::connection('central')->statement("ALTER TABLE tenant_subscriptions ADD CONSTRAINT tenant_subscriptions_deleted_requires_end_check CHECK (status <> 'deleted' OR ends_at IS NOT NULL)");
   }

   public function down(): void {
      DB::connection('central')->statement('ALTER TABLE tenant_subscriptions DROP CONSTRAINT IF EXISTS tenant_subscriptions_deleted_requires_end_check');
      DB::connection('central')->statement('ALTER TABLE tenant_subscriptions DROP CONSTRAINT IF EXISTS tenant_subscriptions_status_check');
      DB::connection('central')->statement('ALTER TABLE tenant_subscriptions DROP CONSTRAINT IF EXISTS tenant_subscriptions_billing_period_check');
   }
};
