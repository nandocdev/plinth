<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_queue_context_runs', function (Blueprint $table): void {
         $table->id();
         $table->unsignedBigInteger('dispatched_by_user_id')->nullable();
         $table->string('requested_tenant_id');
         $table->string('restored_tenant_id')->nullable();
         $table->string('status', 40);
         $table->text('error_message')->nullable();
         $table->timestamp('processed_at')->nullable();
         $table->timestamps();

         $table->index(['requested_tenant_id', 'status'], 'tqcr_requested_status_idx');
         $table->index(['created_at'], 'tqcr_created_idx');
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_queue_context_runs');
   }
};
