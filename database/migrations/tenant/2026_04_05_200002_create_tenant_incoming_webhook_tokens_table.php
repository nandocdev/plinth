<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_incoming_webhook_tokens', function (Blueprint $table): void {
         $table->id();
         $table->string('name');
         $table->string('token', 64)->unique();
         $table->boolean('is_active')->default(true);
         $table->timestamp('last_used_at')->nullable();
         $table->timestamps();

         $table->index(['token', 'is_active']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_incoming_webhook_tokens');
   }
};
