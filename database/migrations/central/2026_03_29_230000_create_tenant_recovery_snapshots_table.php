<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_recovery_snapshots', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->string('operation', 20)->index();
         $table->string('status', 20)->index();
         $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
         $table->foreignId('source_snapshot_id')->nullable()->constrained('tenant_recovery_snapshots')->nullOnDelete();
         $table->string('database_dump_path')->nullable();
         $table->string('storage_archive_path')->nullable();
         $table->string('manifest_path')->nullable();
         $table->timestamp('started_at')->nullable();
         $table->timestamp('completed_at')->nullable();
         $table->text('error_message')->nullable();
         $table->jsonb('meta')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
         $table->index(['tenant_id', 'operation', 'status']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_recovery_snapshots');
   }
};
