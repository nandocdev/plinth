# Central Module: Notification (Sistema de Alertas Globales)

## 1. Propósito
El módulo **Notification** en el contexto Central es el encargado de orquestar la comunicación saliente (principalmente vía email) hacia los administradores globales y socios ante eventos críticos que ocurren en el ecosistema. Su objetivo es mantener a los stakeholders informados sobre el crecimiento y estado de la plataforma.

## 2. Arquitectura de Mensajería

### 2.1 Notificaciones Canónicas
- **`CentralEventMailNotification`**: Una clase de notificación genérica y altamente configurable que permite enviar alertas por email reutilizando una estructura visual consistente para el panel administrativo.

## 3. Capa de Lógica (Acciones)
- **`ListCentralNotificationRecipientsAction`**: Recupera la lista de administradores que deben recibir alertas de sistema según su rol (ej. `SuperAdmin` para nuevos tenants, `BillingAdmin` para suscripciones).

## 4. Orquestación (Listeners)
El módulo opera principalmente de forma reactiva, escuchando eventos de otros módulos centrales:

- **`SendNewTenantNotificationListener`**: Reacciona a `TenantCreatedFromCentral`. Notifica a los administradores cuando un nuevo cliente se une a la plataforma.
- **`SendSubscriptionCreatedNotificationListener`**: Informa sobre la activación de nuevos planes comerciales.
- **`SendSubscriptionUpdatedNotificationListener`**: Alerta sobre cambios en las condiciones de contratación de los clientes.
- **`SendSubscriptionDeletedNotificationListener`**: Notifica sobre cancelaciones de suscripciones, permitiendo acciones rápidas de retención.

## 5. Canales de Entrega
Actualmente, el módulo está configurado para:
- **Mail**: Canal principal para reportes y alertas.
- **Database (extensible)**: Capacidad para persistir notificaciones en la tabla `notifications` global para ser visualizadas en un futuro componente de "Campana de Notificaciones" en el panel central.

## 6. Seguridad y Configuración
- Las notificaciones son enviadas de forma asíncrona mediante colas (`ShouldQueue`) para no penalizar el tiempo de respuesta de las acciones de usuario.
- Los destinatarios se filtran estrictamente por permisos, asegurando que un administrador de soporte no reciba alertas de facturación a menos que tenga el rol adecuado.

## 7. Consideraciones Técnicas
- El diseño de los emails utiliza componentes Blade compartidos para garantizar que la marca Plinth se mantenga consistente en todas las comunicaciones globales.
- Se implementan límites de frecuencia (throttling) internos en ciertos eventos para evitar el spam administrativo durante procesos de carga masiva de datos.
