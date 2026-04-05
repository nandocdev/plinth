<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_csv_transfer_runs', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->unsignedBigInteger('requested_by_user_id')->nullable();
         $table->string('type', 20);
         $table->string('status', 20)->index();
         $table->string('source_disk', 50)->nullable();
         $table->string('source_path', 2048)->nullable();
         $table->string('result_disk', 50)->nullable();
         $table->string('result_path', 2048)->nullable();
         $table->unsignedInteger('total_rows')->default(0);
         $table->unsignedInteger('processed_rows')->default(0);
         $table->timestamp('started_at')->nullable();
         $table->timestamp('completed_at')->nullable();
         $table->text('error_message')->nullable();
         $table->jsonb('meta')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
         $table->index(['tenant_id', 'type', 'status', 'created_at'], 'tenant_csv_runs_tenant_type_status_created_idx');
      });

      DB::statement("create unique index tenant_csv_transfer_runs_active_unique on tenant_csv_transfer_runs (tenant_id, type) where status in ('pending', 'running')");
   }

   public function down(): void {
      DB::statement('drop index if exists tenant_csv_transfer_runs_active_unique');
      Schema::dropIfExists('tenant_csv_transfer_runs');
   }
};
