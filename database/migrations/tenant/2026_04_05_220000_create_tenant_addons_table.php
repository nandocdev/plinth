<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      if (Schema::hasTable('tenant_addons')) {
         return;
      }

      Schema::create('tenant_addons', function (Blueprint $table): void {
         $table->id();
         $table->string('addon_slug', 80)->unique();
         $table->boolean('is_active')->default(false);
         $table->jsonb('config')->default('{}');
         $table->timestamp('installed_at')->nullable();
         $table->timestamp('uninstalled_at')->nullable();
         $table->timestamps();

         $table->index('is_active');
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_addons');
   }
};
