# Central Module: Admin Authorization (Gestión de Roles y Permisos)

## 1. Propósito
El módulo **Admin Authorization** es el responsable de gestionar el Control de Acceso Basado en Roles (RBAC) para los administradores del panel global (Central). Utiliza la infraestructura de `spatie/laravel-permission` pero la especializa mediante Enums y lógica de negocio centralizada para garantizar un control estricto sobre las capacidades administrativas.

## 2. Arquitectura del RBAC Administrativo

### 2.1 Roles y Permisos (Enums)
En lugar de depender exclusivamente de strings en la base de datos, el sistema define roles y permisos canónicos:
- **`AdminRole`**: Enum que define los roles del sistema (ej. `SuperAdmin`, `BillingAdmin`, `SupportAdmin`, `ReadonlyAdmin`) y mapea qué permisos otorga cada uno.
- **`AdminPermission`**: Enum que define los "átomos" de seguridad (ej. `tenants.manage`, `billing.manage`, `logs.view`).

## 3. Capa de Lógica (Acciones)

### 3.1 Gestión de Privilegios
- **`AssignAdminRoleAction`**: Valida y asigna un rol de `AdminRole` a un usuario de Central, asegurando que se apliquen todos los permisos asociados de forma atómica.
- **`RevokeAdminRoleAction`**: Retira privilegios administrativos de forma segura.
- **`ListAdminsAction`**: Facilita la visualización y gestión de la jerarquía de administradores y sus roles actuales.

## 4. Middleware de Acceso
- **`EnsureCentralAdminHasRole`**: Proporciona una capa de seguridad a nivel de ruta. Permite restringir el acceso solicitando uno o más roles específicos.
    ```php
    Route::middleware('central.role:super_admin,billing_admin')->group(...);
    ```

## 5. Capa de Presentación (UI)

### 5.1 Componentes Livewire
- **`AdminRoleCrud`**: Interfaz administrativa para la gestión de roles.
- **`AssignRoleForm`**: Formulario reactivo que utiliza los Enums para presentar las opciones de roles disponibles de forma tipada y segura.

## 6. Seguridad y Autorización

### 6.1 Guard Central
Este módulo opera estrictamente sobre el guard `central`, separando completamente la lógica de permisos de los administradores globales de la lógica de permisos de los usuarios de los tenants.

### 6.2 Policies
- **`AdminRolePolicy`**: Protege las acciones de gestión de roles (ver, asignar, revocar). Estas se registran como Gates en el Service Provider con prefijos específicos (`admin-roles.*`).

## 7. Consideraciones para Single-DB
- Dado que este módulo gestiona la seguridad del "Landlord" (panel central), las tablas de roles y permisos residen en el esquema global.
- Es crítico asegurar que las consultas de autorización administrativa **no se vean afectadas por los Scopes de Tenancy**, ya que un administrador central debe poder operar independientemente de cualquier `tenant_id`.
