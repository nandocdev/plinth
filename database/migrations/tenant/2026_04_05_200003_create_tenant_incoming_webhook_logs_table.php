<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_incoming_webhook_logs', function (Blueprint $table): void {
         $table->id();
         $table->foreignId('tenant_incoming_webhook_token_id')
            ->constrained('tenant_incoming_webhook_tokens')
            ->cascadeOnDelete();
         $table->string('status', 20)->default('received'); // received|processed|failed
         $table->string('source_ip', 45)->nullable();
         $table->jsonb('headers')->default('{}');
         $table->jsonb('payload')->default('{}');
         $table->integer('response_status')->default(200);
         $table->text('processing_error')->nullable();
         $table->timestamp('processed_at')->nullable();
         $table->timestamps();

         $table->index(['tenant_incoming_webhook_token_id', 'status']);
         $table->index('created_at');
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_incoming_webhook_logs');
   }
};
