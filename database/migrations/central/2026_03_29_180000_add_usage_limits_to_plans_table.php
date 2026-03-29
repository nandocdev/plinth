<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::table('plans', function (Blueprint $table): void {
         $table->unsignedInteger('max_users_soft')->nullable()->after('features');
         $table->unsignedInteger('max_users_hard')->nullable()->after('max_users_soft');
         $table->unsignedInteger('max_storage_mb_soft')->nullable()->after('max_users_hard');
         $table->unsignedInteger('max_storage_mb_hard')->nullable()->after('max_storage_mb_soft');
      });

      DB::statement('ALTER TABLE plans ADD CONSTRAINT plans_users_limits_check CHECK (max_users_soft IS NULL OR max_users_hard IS NULL OR max_users_hard >= max_users_soft)');
      DB::statement('ALTER TABLE plans ADD CONSTRAINT plans_storage_limits_check CHECK (max_storage_mb_soft IS NULL OR max_storage_mb_hard IS NULL OR max_storage_mb_hard >= max_storage_mb_soft)');
   }

   public function down(): void {
      DB::statement('ALTER TABLE plans DROP CONSTRAINT IF EXISTS plans_users_limits_check');
      DB::statement('ALTER TABLE plans DROP CONSTRAINT IF EXISTS plans_storage_limits_check');

      Schema::table('plans', function (Blueprint $table): void {
         $table->dropColumn([
            'max_users_soft',
            'max_users_hard',
            'max_storage_mb_soft',
            'max_storage_mb_hard',
         ]);
      });
   }
};
