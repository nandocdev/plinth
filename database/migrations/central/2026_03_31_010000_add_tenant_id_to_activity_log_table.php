<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::table('activity_log', function (Blueprint $table): void {
         if (! Schema::hasColumn('activity_log', 'tenant_id')) {
            $table->string('tenant_id')->nullable()->after('event')->index();
         }
      });
   }

   public function down(): void {
      Schema::table('activity_log', function (Blueprint $table): void {
         if (Schema::hasColumn('activity_log', 'tenant_id')) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
         }
      });
   }
};
