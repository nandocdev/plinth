<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea las tablas de Spatie Permission en la base de datos CENTRAL (guard: 'central').
 * Separadas de las tablas tenant — mismos nombres de tabla, distinta conexión de BD.
 * teams = false para central (admins son globales, sin sub-equipos en este contexto).
 */
return new class extends Migration {
   public function up(): void {
      $tableNames = config('permission.table_names');
      $columnNames = config('permission.column_names');
      $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
      $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

      throw_if(
         empty($tableNames),
         'Error: config/permission.php no cargado. Ejecuta [php artisan config:clear] e intenta de nuevo.'
      );

      Schema::create($tableNames['permissions'], static function (Blueprint $table): void {
         $table->id();
         $table->string('name');
         $table->string('guard_name');
         $table->timestamps();
         $table->unique(['name', 'guard_name']);
      });

      Schema::create($tableNames['roles'], static function (Blueprint $table): void {
         $table->id();
         $table->string('name');
         $table->string('guard_name');
         $table->timestamps();
         $table->unique(['name', 'guard_name']);
      });

      Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission): void {
         $table->unsignedBigInteger($pivotPermission);
         $table->string('model_type');
         $table->unsignedBigInteger($columnNames['model_morph_key']);
         $table->index([$columnNames['model_morph_key'], 'model_type'], 'central_model_has_perms_model_id_type_index');
         $table->foreign($pivotPermission)
            ->references('id')
            ->on($tableNames['permissions'])
            ->cascadeOnDelete();
         $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'central_model_has_perms_primary');
      });

      Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole): void {
         $table->unsignedBigInteger($pivotRole);
         $table->string('model_type');
         $table->unsignedBigInteger($columnNames['model_morph_key']);
         $table->index([$columnNames['model_morph_key'], 'model_type'], 'central_model_has_roles_model_id_type_index');
         $table->foreign($pivotRole)
            ->references('id')
            ->on($tableNames['roles'])
            ->cascadeOnDelete();
         $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'central_model_has_roles_primary');
      });

      Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission): void {
         $table->unsignedBigInteger($pivotPermission);
         $table->unsignedBigInteger($pivotRole);
         $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
         $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
         $table->primary([$pivotPermission, $pivotRole], 'central_role_has_perms_primary');
      });

      app('cache')
         ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
         ->forget(config('permission.cache.key'));
   }

   public function down(): void {
      $tableNames = config('permission.table_names');

      Schema::dropIfExists($tableNames['role_has_permissions']);
      Schema::dropIfExists($tableNames['model_has_roles']);
      Schema::dropIfExists($tableNames['model_has_permissions']);
      Schema::dropIfExists($tableNames['roles']);
      Schema::dropIfExists($tableNames['permissions']);
   }
};
