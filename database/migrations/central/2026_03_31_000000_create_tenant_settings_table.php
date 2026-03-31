<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_settings', function (Blueprint $table): void {
         $table->id();
         $table->string('tenant_id');
         $table->string('company_name', 150);
         $table->string('legal_name', 150)->nullable();
         $table->string('support_email', 150)->nullable();
         $table->string('locale', 10)->default('es');
         $table->string('timezone', 100)->default('UTC');
         $table->string('currency', 3)->default('USD');
         $table->jsonb('branding')->default(DB::raw("'{}'::jsonb"));
         $table->jsonb('preferences')->default(DB::raw("'{}'::jsonb"));
         $table->timestamps();

         $table->unique('tenant_id');
         $table->index(['locale', 'timezone']);
         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_settings');
   }
};
