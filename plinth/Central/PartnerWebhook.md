# Central Module: Partner Webhook (Integraciones Externas)

## 1. Propósito
El módulo **Partner Webhook** permite que sistemas externos de socios y aliados se integren con el ecosistema Plinth de forma reactiva. Proporciona una infraestructura para enviar notificaciones automáticas (webhooks salientes) cuando ocurren eventos clave de negocio, como la creación de nuevos clientes o cambios en sus suscripciones.

## 2. Arquitectura de Datos

### 2.1 Modelos Principales
- **`PartnerWebhookEndpoint`**: Representa una URL de destino configurada por un socio para recibir eventos. Almacena la URL, una clave secreta para firma (HMAC), los eventos suscritos y su estado (activo/inactivo).
- **`PartnerWebhookDelivery`**: Registro histórico de cada envío realizado. Almacena el payload enviado, el código de respuesta del servidor destino (ej. 200, 500), el número de intentos y el tiempo de respuesta.

## 3. Capa de Lógica (Acciones)

### 3.1 Gestión de Endpoints
- **`CreatePartnerWebhookEndpointAction`**: Registra un nuevo destino de notificaciones y genera un secreto de firma único.
- **`ListPartnerWebhookEndpointsAction`**: Listado administrativo de todas las integraciones configuradas.
- **`TogglePartnerWebhookEndpointStatusAction`**: Permite desactivar temporalmente el envío de notificaciones a un endpoint específico.

### 3.2 Procesamiento de Envíos
- **`QueuePartnerWebhookDeliveriesAction`**: Identifica qué socios están suscritos a un evento que acaba de ocurrir y pone en cola los procesos de envío.
- **`RetryPartnerWebhookDeliveryAction`**: Permite reintentar manualmente un envío que falló previamente.
- **`ListPartnerWebhookDeliveriesAction`**: Proporciona una bitácora detallada de todos los intentos de envío para monitoreo y depuración.

## 4. Tareas Asíncronas (Jobs)
- **`DispatchPartnerWebhookDeliveryJob`**: Ejecuta la petición HTTP POST hacia el socio. Incluye lógica de firma de payload para que el destinatario pueda verificar que la data proviene de Plinth.

## 5. Orquestación (Listeners)
El módulo se activa automáticamente ante cambios en el sistema central:
- **`QueueTenantCreatedPartnerWebhookListener`**: Dispara webhooks cuando se aprovisiona un nuevo cliente.
- **`QueueSubscriptionCreated/Updated/DeletedPartnerWebhookListener`**: Notifica cambios comerciales a los sistemas de los socios integrados.

## 6. Capa de Presentación (UI)

### 6.1 Componentes Livewire
- **`WebhookEndpoints`**: Interfaz para configurar las URLs de destino y seleccionar los eventos de interés.
- **`WebhookDeliveries`**: Visor de trazabilidad que permite ver el éxito o error de cada notificación enviada.
- **`PartnerWebhookCrud`**: Panel principal para la administración de integraciones.

## 7. Seguridad y Autorización

### 7.1 Firma de Payloads (HMAC)
Cada petición enviada incluye un encabezado `X-Plinth-Signature`. El socio utiliza el secreto compartido para calcular el hash del cuerpo de la petición y validar su integridad.

### 7.2 Policies
- **`PartnerWebhookEndpointPolicy`**: Controla quién puede gestionar las integraciones (`central.partners.webhooks.manage`).
- **`PartnerWebhookDeliveryPolicy`**: Permite la revisión de logs de entrega.

## 8. Consideraciones Técnicas
- El sistema utiliza **Exponential Backoff** para reintentos automáticos en caso de que el servidor del socio esté temporalmente fuera de servicio.
- Los envíos se realizan de forma totalmente asíncrona mediante Redis para no bloquear el flujo principal de la aplicación.
