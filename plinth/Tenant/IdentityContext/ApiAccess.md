# Tenant Module: API Access (Acceso programático para Clientes)

## 1. Propósito
El módulo **ApiAccess** permite a los usuarios del tenant interactuar con la plataforma de forma programática. Proporciona las herramientas para generar tokens de API (API Keys) seguros, permitiendo la automatización de tareas y la integración del tenant con sus propios sistemas internos o herramientas de terceros.

## 2. Arquitectura de Seguridad (Sanctum)
Plinth utiliza **Laravel Sanctum** para la emisión de tokens. Cada token generado está vinculado de forma única al par `(tenant_id, user_id)`.

## 3. Capa de Lógica (Acciones)
- **`IssueTenantApiTokenAction`**: Genera un nuevo token de acceso. Permite definir un nombre para el token y, opcionalmente, capacidades (scopes) restringidas para limitar el radio de acción de la API Key.
- **`RevokeCurrentTenantApiTokenAction`**: Invalida un token específico, bloqueando inmediatamente el acceso programático asociado.

## 4. Capa de Presentación (UI)

### 4.1 Componentes Livewire
- **`ApiTokenManager`**: Interfaz donde los usuarios con permisos adecuados pueden crear nuevos tokens, visualizar sus prefijos para identificación y eliminarlos. Por seguridad, el token completo (plain-text) solo se muestra una vez al momento de la creación.

## 5. Autenticación de API
Las peticiones externas deben incluir el token en el encabezado `Authorization: Bearer {token}`. El middleware de Sanctum valida el token y reconstruye el contexto del usuario y del tenant para que los Global Scopes se apliquen correctamente a la petición de API.

## 6. Seguridad y Aislamiento
- **Scopes de API:** Los tokens pueden tener permisos limitados (ej. `read-only`) independientemente de los roles del usuario, siguiendo el principio de menor privilegio.
- **Single-DB Compliance:** Los tokens de Sanctum en Plinth incluyen el `tenant_id` en la tabla `personal_access_tokens` para garantizar que la validación sea eficiente y segura.

## 7. Consideraciones Técnicas
- El sistema registra el último uso (`last_used_at`) de cada token para facilitar la auditoría y la detección de claves inactivas.
- Se recomienda rotar los tokens periódicamente.
