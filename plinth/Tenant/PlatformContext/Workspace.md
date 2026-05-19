# Tenant Module: Workspace (Espacio de Trabajo y Perfil)

## 1. Propósito
El módulo **Workspace** es la puerta de entrada para cada usuario dentro de su organización. Gestiona la experiencia individual del usuario (perfil, seguridad personal) y proporciona la vista consolidada (Dashboard) de la actividad y estado de su espacio de trabajo dentro del tenant.

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Perfil y Seguridad
- **`UpdateProfileAction`**: Permite al usuario actualizar su información básica (nombre, avatar, preferencias de idioma).
- **`UpdatePasswordAction`**: Gestiona el cambio seguro de credenciales de acceso.
- **`EnableTenantTwoFactorAction`** / **`DisableTenantTwoFactorAction`**: Permite a los usuarios del tenant activar la autenticación de dos factores (MFA) para su propia cuenta.
- **`ConfirmTenantTwoFactorAction`** / **`RegenerateTenantTwoFactorRecoveryCodesAction`**: Completa el flujo de seguridad de 2FA.

### 2.2 Visualización de Datos
- **`GetTenantDashboardDataAction`**: Orquesta la recolección de métricas rápidas y actividad reciente para poblar la pantalla de inicio del usuario cuando accede a su panel.

## 3. Capa de Presentación (UI)

### 3.1 Componentes Livewire
- **`UserDashboard`**: La vista principal del sistema para los usuarios del tenant, con resúmenes dinámicos y accesos directos.
- **`UserProfileSettings`**: Interfaz para la autogestión de la cuenta del usuario.
- **`SecuritySettings`**: Panel dedicado a la configuración de 2FA y sesiones activas.

## 4. Seguridad y Autorización
- **Aislamiento de Perfil:** Un usuario solo puede editar su propio perfil. La lógica de actualización incluye validaciones de propiedad y contexto de tenant.
- **2FA por Tenant:** El sistema permite que cada tenant defina si el 2FA es opcional u obligatorio para sus miembros, integrándose con el middleware de seguridad del `AuthenticationModule`.

## 5. Consideraciones de Single-DB
- El dashboard utiliza consultas optimizadas con el Global Scope de `tenant_id` para asegurar que el resumen de actividad sea veraz y privado.
- Los avatares y archivos de perfil se gestionan mediante el módulo de `FileUpload`, manteniendo el aislamiento físico de los medios por cliente.

## 6. Consideraciones Técnicas
- El dashboard implementa estrategias de **Lazy Loading** para los componentes de métricas más pesados, asegurando que la primera carga del workspace sea siempre rápida.
