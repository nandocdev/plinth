<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_webhook_deliveries', function (Blueprint $table): void {
         $table->id();
         $table->foreignId('tenant_webhook_endpoint_id')
            ->constrained('tenant_webhook_endpoints')
            ->cascadeOnDelete();
         $table->string('event');
         $table->uuid('delivery_uuid')->unique();
         $table->jsonb('payload')->default('{}');
         $table->string('status', 20)->default('queued'); // queued|processing|delivered|failed
         $table->integer('attempts')->default(0);
         $table->integer('max_attempts')->default(5);
         $table->integer('response_status')->nullable();
         $table->text('response_body')->nullable();
         $table->text('last_error')->nullable();
         $table->timestamp('delivered_at')->nullable();
         $table->timestamp('next_retry_at')->nullable();
         $table->timestamps();

         $table->index(['tenant_webhook_endpoint_id', 'status']);
         $table->index('status');
         $table->index('next_retry_at');
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_webhook_deliveries');
   }
};
