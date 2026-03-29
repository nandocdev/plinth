<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_invoices', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->foreignId('subscription_id')
            ->nullable()
            ->constrained('tenant_subscriptions')
            ->nullOnDelete();
         $table->string('invoice_number', 50)->unique();
         $table->string('currency', 10)->default('USD');
         $table->unsignedInteger('amount_cents');
         $table->string('status', 20)->default('open');
         $table->string('billing_period', 20)->nullable();
         $table->text('description')->nullable();
         $table->timestamp('paid_at')->nullable();
         $table->jsonb('meta')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
         $table->index(['tenant_id', 'created_at']);
         $table->index(['tenant_id', 'status']);
      });

      DB::statement("ALTER TABLE tenant_invoices ADD CONSTRAINT chk_tenant_invoices_status CHECK (status IN ('open', 'paid', 'void'))");
   }

   public function down(): void {
      Schema::dropIfExists('tenant_invoices');
   }
};
