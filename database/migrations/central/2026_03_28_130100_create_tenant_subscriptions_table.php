<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_subscriptions', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->foreignId('plan_id')->constrained('plans')->cascadeOnUpdate()->restrictOnDelete();
         $table->string('billing_period', 20)->default('monthly');
         $table->string('status', 20)->default('trialing')->index();
         $table->timestamp('trial_ends_at')->nullable()->index();
         $table->timestamp('starts_at')->nullable();
         $table->timestamp('ends_at')->nullable()->index();
         $table->unsignedInteger('price_snapshot_cents');
         $table->string('external_id', 150)->nullable()->unique();
         $table->jsonb('meta')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
         $table->unique('tenant_id');
         $table->index(['plan_id', 'status']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_subscriptions');
   }
};
