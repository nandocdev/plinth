# Tenant Module: Authorization (Roles y Permisos del Cliente)

## 1. Propósito
El módulo **Authorization** gestiona el Control de Acceso Basado en Roles (RBAC) dentro de cada tenant. Permite definir qué acciones puede realizar cada usuario de la organización cliente, utilizando la infraestructura de `spatie/laravel-permission` aislada por `tenant_id`.

## 2. Arquitectura del RBAC Multi-tenant

### 2.1 Roles y Permisos (Enums)
- **`TenantRole`**: Define los roles predeterminados para los clientes (ej. `Admin`, `Manager`, `Member`, `Guest`). Cada rol mapea internamente un conjunto de capacidades.
- Los permisos son granulares (ej. `users.view`, `settings.update`, `billing.view`).

### 2.2 Aislamiento de Roles
A diferencia de los roles centrales, los roles de este módulo están vinculados al tenant. Esto significa que un rol "Manager" en el Tenant A es una entidad distinta al rol "Manager" en el Tenant B, permitiendo personalizaciones por cliente si fuera necesario.

## 3. Capa de Lógica (Acciones)
- **`AssignRoleToUserAction`**: Asocia un rol de `TenantRole` a un usuario del tenant, sincronizando sus permisos correspondientes.

## 4. Middleware de Seguridad
- Las rutas del tenant utilizan el middleware de autorización para validar capacidades antes de permitir el acceso:
    ```php
    Route::middleware('can:users.manage')->group(...);
    ```

## 5. Capa de Presentación (UI)
- **`RoleManagement`**: Interfaz opcional (según el plan del tenant) para visualizar los permisos asociados a cada rol.
- Integrado en los formularios de `UserManagement` para la asignación de roles a miembros del equipo.

## 6. Seguridad y Aislamiento
- **Single-DB Compliance:** Plinth asegura que las tablas de roles y permisos de Spatie incluyan la columna `tenant_id` y utilicen el trait de filtrado para evitar que los roles de un cliente se mezclen con otros.

## 7. Consideraciones Técnicas
- Los roles se inicializan automáticamente mediante el `SeedDefaultRolesAction` cuando un nuevo tenant finaliza su registro.
- Se recomienda el uso de **Policies** de Laravel para toda la lógica de autorización compleja, delegando en el sistema de permisos la validación de capacidades atómicas.
