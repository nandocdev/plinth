<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::table('domains', function (Blueprint $table): void {
         $table->timestamp('verified_at')->nullable()->index();
      });
   }

   public function down(): void {
      Schema::table('domains', function (Blueprint $table): void {
         $table->dropColumn('verified_at');
      });
   }
};