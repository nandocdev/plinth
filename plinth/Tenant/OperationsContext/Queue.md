# Tenant Module: Queue (Gestión de Tareas Asíncronas del Cliente)

## 1. Propósito
El módulo **Queue** gestiona la ejecución de procesos pesados en segundo plano (background) específicos para cada organización. Aunque la infraestructura de colas es compartida en Plinth (Redis), este módulo asegura que las tareas asíncronas respeten el contexto del tenant y se ejecuten con el aislamiento de datos correspondiente.

## 2. Arquitectura Multi-tenant de Colas
Plinth utiliza **Laravel Horizon** para el monitoreo global de las colas, pero cada Job disparado desde el contexto de un tenant incluye automáticamente el `tenantId` en su payload.

## 3. Procesamiento de Tareas
Cuando un worker de Laravel procesa un Job perteneciente a un tenant:
1. El middleware de cola de Plinth detecta el `tenantId`.
2. Se establece el contexto del tenant de forma similar a una petición HTTP.
3. El Job se ejecuta, y cualquier consulta Eloquent dentro del Job incluye automáticamente el Global Scope de `tenant_id`.

## 4. Tipos de Tareas Comunes
- Envío masivo de notificaciones.
- Procesamiento de archivos y medios.
- Sincronización con APIs de terceros mediante webhooks salientes.
- Generación de reportes pesados y snapshots de métricas.

## 5. Capa de Presentación (UI)

### 5.1 Monitoreo para el Cliente
- **`QueueMonitor`**: Una interfaz simplificada (si el plan del tenant lo permite) donde los administradores pueden ver el estado de sus tareas pendientes, procesadas o fallidas, sin tener acceso a la infraestructura global de Redis o Horizon.

## 6. Seguridad y Aislamiento
- **Aislamiento de Recursos:** El sistema permite configurar límites de concurrencia (rate limiting) por tenant para evitar que un solo cliente sature todos los workers de la plataforma, garantizando un servicio justo (Fair Usage Policy) para todos los usuarios en el esquema Single-DB.

## 7. Consideraciones Técnicas
- Todos los Jobs deben implementar la interfaz `ShouldQueue` y utilizar los Traits de Plinth para la persistencia del contexto de tenencia.
- Se implementa lógica de reintentos con retraso exponencial para manejar fallos temporales en servicios externos del cliente.
