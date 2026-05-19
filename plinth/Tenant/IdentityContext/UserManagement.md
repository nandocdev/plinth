# Tenant Module: User Management (Gestión de Usuarios del Cliente)

## 1. Propósito
El módulo **User Management** permite a los administradores del tenant gestionar su propio equipo de trabajo. Proporciona herramientas para invitar, editar y dar de baja usuarios, así como para asignarles roles y permisos específicos dentro de la organización del cliente.

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Usuarios
- **`CreateTenantUserAction`**: Crea un nuevo usuario vinculado al tenant actual y le asigna un rol inicial.
- **`UpdateTenantUserAction`**: Permite modificar la información de perfil, estado (activo/inactivo) y roles del usuario.
- **`DeleteTenantUserAction`**: Elimina a un usuario de la organización (generalmente mediante soft-delete para preservar la integridad de los logs históricos).
- **`SeedDefaultRolesAction`**: Utilizado durante el aprovisionamiento inicial del tenant para crear los roles estándar (Admin, Manager, User) específicos para ese cliente.

## 3. Capa de Presentación (UI)

### 3.1 Componentes Livewire
- **`UserCrud`**: Interfaz maestra para listar y gestionar los usuarios del tenant.
- **`InviteUserForm`**: Formulario especializado para invitar nuevos miembros vía email.
- **`UserEditProfile`**: Componente para que cada usuario gestione su propia información.

## 4. Seguridad y Autorización

### 4.1 Policies
- **`TenantUserPolicy`**: Restringe la gestión de usuarios solo a aquellos que tienen el rol de Administrador en el tenant. Evita que un usuario pueda eliminarse a sí mismo si es el último administrador activo.

## 5. Consideraciones de Single-DB
- Al utilizar el trait `BelongsToTenant`, todas las operaciones de este módulo están confinadas al universo de datos del cliente actual. 
- Los índices compuestos `[tenant_id, email]` garantizan que las búsquedas de usuarios sean eficientes incluso con millones de registros totales en la base de datos compartida.
