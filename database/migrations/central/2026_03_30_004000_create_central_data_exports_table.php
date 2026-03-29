<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('central_data_exports', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
         $table->uuid('export_uuid')->unique();
         $table->string('format', 20)->default('zip');
         $table->string('status', 20)->index();
         $table->boolean('include_activity_log')->default(true);
         $table->string('file_path')->nullable();
         $table->string('checksum_sha256', 64)->nullable();
         $table->unsignedBigInteger('size_bytes')->nullable();
         $table->timestamp('started_at')->nullable();
         $table->timestamp('completed_at')->nullable();
         $table->text('error_message')->nullable();
         $table->jsonb('meta')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
         $table->index(['tenant_id', 'status', 'created_at']);
      });

      DB::statement("create unique index central_data_exports_active_tenant_unique on central_data_exports (tenant_id) where status in ('pending', 'running')");
   }

   public function down(): void {
      DB::statement('drop index if exists central_data_exports_active_tenant_unique');
      Schema::dropIfExists('central_data_exports');
   }
};