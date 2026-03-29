<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('plans', function (Blueprint $table): void {
         $table->id();
         $table->string('name', 120)->unique();
         $table->string('slug', 120)->unique();
         $table->unsignedInteger('price_monthly_cents');
         $table->unsignedInteger('price_yearly_cents')->nullable();
         $table->unsignedSmallInteger('trial_days')->default(0);
         $table->jsonb('features')->default(json_encode([]));
         $table->boolean('is_active')->default(true)->index();
         $table->unsignedSmallInteger('sort_order')->default(0);
         $table->timestamps();

         $table->index(['is_active', 'sort_order']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('plans');
   }
};
