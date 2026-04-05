<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::table('domains', function (Blueprint $table): void {
         if (! Schema::hasColumn('domains', 'ssl_status')) {
            $table->string('ssl_status', 24)->default('not_requested')->index();
         }

         if (! Schema::hasColumn('domains', 'ssl_requested_at')) {
            $table->timestamp('ssl_requested_at')->nullable();
         }

         if (! Schema::hasColumn('domains', 'ssl_issued_at')) {
            $table->timestamp('ssl_issued_at')->nullable();
         }

         if (! Schema::hasColumn('domains', 'ssl_expires_at')) {
            $table->timestamp('ssl_expires_at')->nullable();
         }

         if (! Schema::hasColumn('domains', 'ssl_last_error')) {
            $table->text('ssl_last_error')->nullable();
         }
      });
   }

   public function down(): void {
      Schema::table('domains', function (Blueprint $table): void {
         foreach (['ssl_requested_at', 'ssl_issued_at', 'ssl_expires_at', 'ssl_last_error'] as $column) {
            if (Schema::hasColumn('domains', $column)) {
               $table->dropColumn($column);
            }
         }

         if (Schema::hasColumn('domains', 'ssl_status')) {
            $table->dropIndex('domains_ssl_status_index');
            $table->dropColumn('ssl_status');
         }
      });
   }
};
