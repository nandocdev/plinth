<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_impersonation_tokens', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->foreignId('impersonator_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
         $table->string('target_domain', 190);
         $table->string('token_hash', 64)->unique();
         $table->timestamp('expires_at')->index();
         $table->timestamp('used_at')->nullable()->index();
         $table->jsonb('meta')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
         $table->index(['tenant_id', 'expires_at']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_impersonation_tokens');
   }
};
