<?php

declare(strict_types=1);

namespace Database\Seeders\Central\AdminAuthorizationModule;

use App\Central\AdminAuthorizationModule\Enums\AdminPermission;
use App\Central\AdminAuthorizationModule\Enums\AdminRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class CentralRolesPermissionsSeeder extends Seeder {
   public function run(): void {
      // Limpiar cache de Spatie antes de sembrar
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      // 1. Crear todos los permisos con guard 'central'
      foreach (AdminPermission::cases() as $permission) {
         Permission::firstOrCreate(
            ['name' => $permission->value, 'guard_name' => 'central']
         );
      }

      // 2. Crear roles y asignarles permisos
      foreach (AdminRole::cases() as $role) {
         $roleModel = Role::firstOrCreate(
            ['name' => $role->value, 'guard_name' => 'central']
         );

         $permissionNames = array_map(
            fn(AdminPermission $p) => $p->value,
            $role->permissions()
         );

         $roleModel->syncPermissions($permissionNames);
      }
   }
}
