# Tenant Module: Webhook (Integraciones Salientes y Entrantes)

## 1. Propósito
El módulo **Webhook** dota a los clientes de Plinth de capacidades de integración avanzadas. Permite que el tenant envíe notificaciones automáticas a sus propios sistemas (Webhooks Salientes) y que reciba datos de servicios externos (Webhooks Entrantes) para automatizar flujos de trabajo internos.

## 2. Webhooks Salientes (Outbound)

### 2.1 Arquitectura de Datos
- **`TenantWebhookEndpoint`**: Configuración de la URL destino del cliente, eventos suscritos y secreto de firma.
- **`TenantWebhookDelivery`**: Registro histórico de envíos, respuestas y reintentos.

### 2.2 Capa de Lógica (Acciones)
- **`CreateWebhookEndpointAction`** / **`UpdateWebhookEndpointAction`**: Gestión de los destinos de notificación del cliente.
- **`DispatchOutgoingWebhookAction`**: Orquesta el envío asíncrono de datos hacia el sistema del cliente.
- **`RetryWebhookDeliveryAction`**: Permite al cliente reintentar manualmente envíos fallidos.

## 3. Webhooks Entrantes (Inbound)

### 3.1 Seguridad
- **`CreateIncomingTokenAction`**: Genera un token de acceso exclusivo para que sistemas externos puedan enviar datos al tenant de forma segura.
- **`RevokeIncomingTokenAction`**: Invalida el acceso entrante inmediatamente.

### 3.2 Procesamiento
- **`ProcessIncomingWebhookAction`**: Valida el payload recibido de terceros, verifica el token y dispara los eventos internos correspondientes dentro del tenant.

## 4. Capa de Presentación (UI)

### 4.1 Componentes Livewire
- **`WebhookManager`**: Dashboard donde el cliente configura sus endpoints, genera sus tokens de entrada y monitorea el tráfico de red de sus integraciones.

## 5. Seguridad y Aislamiento
- **Aislamiento de Datos:** Todos los webhooks (entrantes y salientes) están filtrados por `tenant_id`. Un token de entrada solo otorga acceso al contexto del tenant que lo generó.
- **Firma HMAC:** Los webhooks salientes incluyen una firma criptográfica para que el cliente pueda validar que la data proviene efectivamente de Plinth.

## 6. Consideraciones Técnicas
- El procesamiento de webhooks salientes se realiza mediante Jobs asíncronos para no afectar el rendimiento de la aplicación.
- Se implementan límites de tasa (rate limiting) para evitar que un flujo masivo de webhooks entrantes degrade el servicio para otros tenants en el esquema Single-DB.
