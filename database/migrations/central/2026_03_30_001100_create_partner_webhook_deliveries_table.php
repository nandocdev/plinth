<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('partner_webhook_deliveries', function (Blueprint $table): void {
         $table->id();
         $table->foreignId('partner_webhook_endpoint_id')->constrained('partner_webhook_endpoints')->cascadeOnDelete();
         $table->string('event', 120);
         $table->string('tenant_id', 64)->nullable();
         $table->uuid('delivery_uuid');
         $table->json('payload');
         $table->string('status', 20)->default('queued');
         $table->unsignedInteger('attempts')->default(0);
         $table->unsignedInteger('max_attempts')->default(5);
         $table->unsignedSmallInteger('response_status')->nullable();
         $table->text('response_body')->nullable();
         $table->text('last_error')->nullable();
         $table->timestamp('delivered_at')->nullable();
         $table->timestamp('next_retry_at')->nullable();
         $table->timestamps();

         $table->index(['status', 'next_retry_at']);
         $table->index(['event', 'created_at']);
         $table->index('tenant_id');
         $table->unique(['partner_webhook_endpoint_id', 'delivery_uuid']);
      });

      DB::statement("ALTER TABLE partner_webhook_deliveries ADD CONSTRAINT partner_webhook_deliveries_status_check CHECK (status IN ('queued', 'processing', 'delivered', 'failed'))");
   }

   public function down(): void {
      Schema::dropIfExists('partner_webhook_deliveries');
   }
};
