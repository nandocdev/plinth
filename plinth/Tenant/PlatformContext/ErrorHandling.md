# Tenant Module: Error Handling (Gestión de Errores del Cliente)

## 1. Propósito
El módulo **Error Handling** garantiza que los fallos técnicos dentro del contexto del tenant se gestionen de forma elegante y segura. Su objetivo es proporcionar retroalimentación útil al usuario final mientras captura datos de diagnóstico precisos para los desarrolladores, manteniendo siempre el aislamiento de datos.

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Disponibilidad
- **`ResolveTenantMaintenanceStatusAction`**: Determina si el espacio de trabajo de un cliente específico debe estar en "Modo Mantenimiento". Esto permite realizar actualizaciones programadas o suspender el acceso a un solo tenant sin afectar al resto de la plataforma global.

## 3. Arquitectura de Errores Multi-tenant
Plinth personaliza el Exception Handler de Laravel para:
- **Detección de Contexto:** Identifica si un error ocurrió dentro de un tenant.
- **Vistas Personalizadas:** Renderiza páginas de error (404, 500) que respetan el branding (colores, logo) del tenant actual.
- **Registro de Errores:** Los errores se registran incluyendo el `tenant_id` en los metadatos del log (vía Sentry o archivos locales), permitiendo filtrar fallos por cliente.

## 4. Capa de Presentación (UI)
- Proporciona las plantillas de error del tenant, las cuales se inyectan dinámicamente en el flujo de respuesta cuando ocurre una excepción no controlada.

## 5. Seguridad y Aislamiento
- **Aislamiento de Información:** Las páginas de error nunca muestran trazas de código o rutas internas del servidor a los usuarios finales del tenant para evitar la exposición de vulnerabilidades.
- **Single-DB Compliance:** La lógica de detección de mantenimiento consulta el estado directamente del registro del tenant en la base de datos única.

## 6. Consideraciones Técnicas
- El sistema utiliza **Middleware de Mantenimiento** que intercepta las peticiones y redirige a la página de "Servicio Temporalmente No Disponible" si el tenant tiene activada la bandera de mantenimiento.
- Integrado con el sistema de logs para alertar a los administradores de Central si un tenant específico presenta una tasa de errores inusualmente alta.
