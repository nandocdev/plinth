<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_provisioning_hook_runs', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->string('hook_name', 100);
         $table->string('driver', 40);
         $table->string('status', 20)->index();
         $table->jsonb('command');
         $table->string('working_directory')->nullable();
         $table->jsonb('environment')->default(json_encode([]));
         $table->integer('exit_code')->nullable();
         $table->longText('output')->nullable();
         $table->longText('error_output')->nullable();
         $table->timestamp('started_at')->nullable();
         $table->timestamp('completed_at')->nullable();
         $table->jsonb('meta')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
         $table->unique(['tenant_id', 'hook_name']);
         $table->index(['tenant_id', 'status']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_provisioning_hook_runs');
   }
};
