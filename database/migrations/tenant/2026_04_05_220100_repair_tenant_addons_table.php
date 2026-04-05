<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      if (! Schema::hasTable('tenant_addons')) {
         return;
      }

      Schema::table('tenant_addons', function (Blueprint $table): void {
         if (! Schema::hasColumn('tenant_addons', 'addon_slug')) {
            $table->string('addon_slug', 80)->nullable()->after('id');
         }

         if (! Schema::hasColumn('tenant_addons', 'is_active')) {
            $table->boolean('is_active')->default(false)->after('addon_slug');
         }

         if (! Schema::hasColumn('tenant_addons', 'config')) {
            $table->jsonb('config')->default('{}')->after('is_active');
         }

         if (! Schema::hasColumn('tenant_addons', 'installed_at')) {
            $table->timestamp('installed_at')->nullable()->after('config');
         }

         if (! Schema::hasColumn('tenant_addons', 'uninstalled_at')) {
            $table->timestamp('uninstalled_at')->nullable()->after('installed_at');
         }

         if (! Schema::hasColumn('tenant_addons', 'created_at')) {
            $table->timestamp('created_at')->nullable()->after('uninstalled_at');
         }

         if (! Schema::hasColumn('tenant_addons', 'updated_at')) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
         }
      });

      DB::table('tenant_addons')
         ->whereNull('addon_slug')
         ->orderBy('id')
         ->get(['id'])
         ->each(function (object $row): void {
            DB::table('tenant_addons')
               ->where('id', $row->id)
               ->update([
                  'addon_slug' => 'legacy-addon-' . $row->id,
                  'updated_at' => now(),
               ]);
         });

      DB::statement("ALTER TABLE tenant_addons ALTER COLUMN addon_slug SET NOT NULL");

      DB::statement(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_indexes
        WHERE tablename = 'tenant_addons'
          AND indexname = 'tenant_addons_addon_slug_unique'
    ) THEN
        CREATE UNIQUE INDEX tenant_addons_addon_slug_unique ON tenant_addons (addon_slug);
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM pg_indexes
        WHERE tablename = 'tenant_addons'
          AND indexname = 'tenant_addons_is_active_index'
    ) THEN
        CREATE INDEX tenant_addons_is_active_index ON tenant_addons (is_active);
    END IF;
END
$$;
SQL);
   }

   public function down(): void {
      // Reparación no reversible de forma segura en tenants existentes.
   }
};
