# Tenant Module: Authentication (Acceso de Usuarios del Cliente)

## 1. Propósito
El módulo **Authentication** en el contexto del Tenant gestiona el acceso seguro de los usuarios finales al panel de su respectiva organización. Implementa flujos de login, registro y gestión de sesiones aislados lógicamente mediante el `tenant_id`, asegurando que los usuarios de un cliente no puedan autenticarse en el entorno de otro.

## 2. Arquitectura de Seguridad

### 2.1 Modelo: `User` (Contexto Tenant)
- Representa a un usuario final del cliente.
- **Aislamiento Single-DB:** Utiliza el trait `BelongsToTenant` para filtrar automáticamente todas las consultas por el `tenant_id` actual.
- Utiliza el guard `web` (configurado para el contexto tenant).

## 3. Capa de Lógica (Acciones)

### 3.1 Procesos de Autenticación
- **`AuthenticateTenantUserAction`**: Valida las credenciales del usuario dentro del contexto de su tenant. Si el usuario existe pero pertenece a otro tenant, la autenticación fallará debido al Global Scope.
- **`RegisterTenantUserAction`**: Permite el auto-registro de nuevos usuarios en el panel del tenant (si la configuración del cliente lo permite).

## 4. Capa de Presentación (UI)

### 4.1 Componentes Livewire
- Proporciona las vistas de:
    - Login del Tenant.
    - Registro de Usuarios.
    - Recuperación de Contraseña.

## 5. Integración con Fortify
Al igual que en Central, se utiliza **Laravel Fortify** pero configurado para el contexto multi-tenant, asegurando que los redirects y la validación de unicidad de email siempre consideren el `tenant_id`.

## 6. Seguridad y Aislamiento
- **Global Scopes:** Todas las validaciones de existencia de usuario incluyen implícitamente `WHERE tenant_id = ?`.
- **Sesiones:** Las sesiones de usuario están vinculadas al dominio o subdominio del tenant para evitar colisiones entre diferentes clientes.

## 7. Consideraciones Técnicas
- El email de un usuario es único **dentro de su tenant**. Plinth permite que un mismo email exista en diferentes tenants como cuentas separadas e independientes (arquitectura Single-DB estricta).
- Utiliza `Remember Me` seguro y protección contra ataques de fuerza bruta por IP y tenant.
