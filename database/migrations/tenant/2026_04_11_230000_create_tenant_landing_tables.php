<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_landings', function (Blueprint $table): void {
         $table->id();
         $table->string('template_key', 50)->default('corporate');
         $table->string('status', 20)->default('draft');
         $table->string('font_family', 40)->default('instrument');
         $table->string('primary_color', 7)->default('#2563eb');
         $table->jsonb('global_settings')->default(DB::raw("'{}'::jsonb"));
         $table->timestamps();

         $table->index(['status', 'updated_at']);
      });

      Schema::create('landing_blocks', function (Blueprint $table): void {
         $table->id();
         $table->foreignId('tenant_landing_id')->constrained('tenant_landings')->cascadeOnDelete();
         $table->string('block_type', 40);
         $table->unsignedInteger('order')->default(0);
         $table->boolean('is_active')->default(true);
         $table->jsonb('settings')->default(DB::raw("'{}'::jsonb"));
         $table->timestamps();

         $table->index(['tenant_landing_id', 'order']);
         $table->index(['tenant_landing_id', 'is_active']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('landing_blocks');
      Schema::dropIfExists('tenant_landings');
   }
};
