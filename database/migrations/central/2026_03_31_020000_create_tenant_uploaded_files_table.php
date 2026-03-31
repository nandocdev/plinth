<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_uploaded_files', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
         $table->string('disk', 50)->default('tenant');
         $table->string('folder', 100)->nullable();
         $table->string('original_name', 255);
         $table->string('stored_name', 255);
         $table->string('stored_path', 2048);
         $table->string('mime_type', 150)->nullable();
         $table->unsignedBigInteger('size_bytes');
         $table->timestamps();

         $table->index(['tenant_id', 'created_at']);
         $table->index(['tenant_id', 'uploaded_by_user_id']);
         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_uploaded_files');
   }
};
