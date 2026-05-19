# Tenant Module: Notification (Alertas y Comunicaciones)

## 1. Propósito
El módulo **Notification** permite la comunicación interna dentro de la organización del cliente. Gestiona tanto las notificaciones de sistema dirigidas a los usuarios del tenant como las alertas externas (emails) vinculadas a procesos de negocio propios del cliente.

## 2. Capa de Lógica (Acciones)
- **`SendTenantNotificationAction`**: Orquesta el envío de una notificación a uno o varios usuarios del tenant. Soporta múltiples canales (Database, Mail).
- **`ListTenantNotificationsAction`**: Recupera el historial de notificaciones del usuario autenticado, filtradas por `tenant_id`.
- **`MarkTenantNotificationAsReadAction`**: Permite a los usuarios marcar sus alertas como leídas, actualizando el estado en la base de datos única.

## 3. Arquitectura de Mensajería
- **`TenantGeneralNotification`**: Una clase base configurable para las alertas dentro del tenant. Asegura que el contenido sea dinámico y relevante para el contexto del cliente.

## 4. Capa de Presentación (UI)

### 4.1 Componentes Livewire
- **`NotificationBell`**: Componente reactivo que muestra el contador de notificaciones no leídas en tiempo real.
- **`NotificationInbox`**: Una bandeja de entrada dedicada para que los usuarios gestionen sus alertas históricas.

## 5. Seguridad y Aislamiento
- **Aislamiento de Datos:** Las notificaciones persistidas en la tabla `notifications` están estrictamente vinculadas al `tenant_id`. Un usuario nunca verá notificaciones de un tenant diferente, incluso si usa el mismo email.

## 6. Consideraciones Técnicas
- El envío de notificaciones por email se realiza mediante colas (`ShouldQueue`) para garantizar la fluidez de la interfaz de usuario.
- El módulo es extensible, permitiendo añadir canales como Slack o WhatsApp mediante la integración de providers adicionales en el futuro.
