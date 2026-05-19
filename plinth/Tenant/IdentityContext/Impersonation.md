# Tenant Module: Impersonation (Consumo de Impersonación)

## 1. Propósito
El módulo **Impersonation** en el contexto del Tenant es el componente receptor que permite a los administradores de la plataforma central acceder al panel de un cliente para labores de soporte técnico. Su función es validar los tokens de acceso generados en Central y establecer una sesión de usuario válida dentro del contexto del tenant de forma segura y efímera.

## 2. Arquitectura de Acceso

### 2.1 Flujo de "Consumo"
1. El administrador de Central llega al dominio del tenant con un token en la URL (ej. `cliente.plinth.com/impersonate/{token}`).
2. El `AcceptTenantImpersonationController` captura la petición.
3. Se valida que el token exista en la tabla global, sea válido para ese tenant específico y no haya expirado.
4. El sistema inicia sesión automáticamente con el usuario destino definido en el token.

## 3. Controladores (Http)
- **`AcceptTenantImpersonationController`**: Valida el token, destruye el token (uso único) y establece la sesión de usuario del tenant.
- **`LeaveTenantImpersonationController`**: Permite al administrador salir del modo impersonación y regresar automáticamente al panel central de Plinth.

## 4. Visualización (UI)
- Durante una sesión de impersonación, Plinth muestra una **barra de aviso persistente** (Banner) en la parte superior del panel del tenant. Esto garantiza la transparencia y alerta al administrador de que está operando con la identidad de otro usuario.

## 5. Seguridad y Auditoría
- **Seguridad:** El token de impersonación es de un solo uso y tiene una vida corta (minutos).
- **Aislamiento:** El administrador central solo puede acceder a los datos de ese `tenant_id` específico durante la sesión. Los Global Scopes se aplican estrictamente.
- **Auditoría:** Todas las acciones realizadas durante una impersonación se registran en el `ActivityLog` del tenant, marcando claramente que el `causer` fue un administrador central actuando en nombre del usuario local.

## 6. Consideraciones Técnicas
- El sistema utiliza un guard de autenticación temporal para evitar que el administrador pierda su propia sesión en el panel central mientras asiste al cliente.
- Requiere que el módulo de `TenantProvisioning` en Central haya generado previamente un token válido.
