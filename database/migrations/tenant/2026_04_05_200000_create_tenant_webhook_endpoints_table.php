<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_webhook_endpoints', function (Blueprint $table): void {
         $table->id();
         $table->string('name');
         $table->string('target_url');
         $table->string('signing_secret', 64);
         $table->jsonb('subscribed_events')->default('[]');
         $table->boolean('is_active')->default(true);
         $table->integer('max_attempts')->default(5);
         $table->timestamps();

         $table->index('is_active');
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_webhook_endpoints');
   }
};
