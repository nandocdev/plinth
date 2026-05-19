# Central Module: Authentication (Seguridad y Acceso Global)

## 1. Propósito
El módulo **Authentication** gestiona el acceso seguro al panel de control central (Landlord). Es el responsable de la autenticación de los administradores del sistema, implementando políticas de seguridad estrictas como la autenticación de dos factores (2FA) y el manejo de sesiones globales, separadas de las sesiones de los tenants.

## 2. Arquitectura de Seguridad

### 2.1 Modelo: `User` (Contexto Central)
- El modelo `User` en este contexto representa a un **Administrador Global**.
- Utiliza el guard `central` para diferenciarlo de los usuarios de los clientes.
- Incluye el trait `HasRoles` (Spatie) para integrarse con el módulo `AdminAuthorization`.
- Implementa `TwoFactorAuthenticatable` (Fortify) para soporte nativo de 2FA.

## 3. Capa de Lógica (Acciones)

### 3.1 Procesos de Acceso
- **`AttemptSystemAdminLoginAction`**: Gestiona el flujo de autenticación, validando credenciales y el estado de 2FA si está habilitado.
- **`RegisterSystemAdminAction`**: Permite la creación de nuevos administradores (generalmente restringido a SuperAdmins).
- **`FindOrCreateSystemAdminAction`**: Utilizado en flujos de invitación o integración con proveedores externos.
- **`ResetUserPassword`**: Orquesta el flujo de recuperación de contraseña mediante tokens seguros.

## 4. Middleware de Seguridad
- **`EnsureSystemAdminHasTwoFactorEnabled`**: Este middleware es crítico. Redirige a los administradores a la configuración de 2FA si el sistema requiere obligatoriamente esta capa de seguridad para su rol o acceso.
- Las rutas del panel central utilizan la combinación `auth:central` y `verified`.

## 5. Integración con Fortify
Plinth utiliza **Laravel Fortify** como motor headless para:
- Registro y Login.
- Restablecimiento de contraseñas.
- Verificación de email.
- Gestión de 2FA (códigos QR, códigos de recuperación).

La configuración específica se encuentra en `AuthenticationModuleServiceProvider` y `FortifyServiceProvider`.

## 6. Dashboard Central
El dashboard administrativo reside técnicamente dentro de las rutas protegidas por este módulo. Orquesta la visualización de:
- Métricas agregadas de salud del sistema (vía `BuildCentralAggregateMetricsAction`).
- Resumen de facturación pendiente.
- Estado de exportaciones en curso.
- Rastro de auditoría reciente.

## 7. Consideraciones Técnicas
- **Aislamiento de Sesión:** Las cookies de sesión del panel central tienen un nombre y dominio configurados para no colisionar con las sesiones de los subdominios de los tenants.
- **Auditoría:** Todos los intentos de login (exitosos y fallidos) son registrados por el módulo de `ActivityLog` para detectar ataques de fuerza bruta.
