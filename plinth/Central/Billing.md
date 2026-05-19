# Central Module: Billing (Gestión de Planes y Facturación)

## 1. Propósito
El módulo **Billing** centraliza la lógica comercial del ecosistema Plinth. Es el responsable de definir la oferta comercial (Planes), gestionar el ciclo de vida de las suscripciones de los tenants y procesar la facturación y pagos, integrando pasarelas externas como dLocal.

## 2. Arquitectura de Datos

### 2.1 Modelos Principales
- **`Plan`**: Define las características, límites y precio de una oferta comercial.
- **`TenantSubscription`**: Vincula un Tenant con un `Plan`, gestionando fechas de inicio, fin y estado de la suscripción.
- **`TenantInvoice`**: Registro de cobros realizados o pendientes para un tenant.
- **`ProcessedWebhook`**: Control de idempotencia para asegurar que un webhook de pago no se procese dos veces.

## 3. Capa de Lógica (Acciones)

### 3.1 Gestión de Planes
- **`ListPlansAction`**: Recupera todos los planes con soporte para búsqueda y ordenamiento.
- **`CreatePlanAction`**: Crea un nuevo plan definiendo nombre, descripción, precio, moneda y frecuencia.
- **`UpdatePlanAction`**: Modifica las condiciones de un plan existente.
- **`DeletePlanAction`**: Elimina un plan (generalmente soft-delete si tiene suscripciones).
- **`ListActivePlansAction`**: Filtra planes disponibles para nuevas contrataciones.

### 3.2 Gestión de Suscripciones
- **`ListSubscriptionsAction`**: Listado administrativo de todas las suscripciones de los tenants.
- **`CreateSubscriptionAction`**: Activa un plan para un tenant específico.
- **`UpdateSubscriptionAction`**: Cambia el plan o las condiciones de una suscripción activa.
- **`DeleteSubscriptionAction`**: Cancela o suspende una suscripción.
- **`SyncSubscriptionLifecycleAction`**: Tarea programada para verificar expiraciones y renovaciones automáticas.

### 3.3 Procesamiento de Pagos (Webhooks dLocal)
- **`DlocalWebhookController`**: Endpoint público que recibe las notificaciones de dLocal.
- **`VerifyDlocalWebhookSignatureAction`**: Valida la autenticidad de la petición mediante la firma de dLocal.
- **`NormalizeDlocalWebhookAction`**: Transforma el payload crudo de dLocal a un formato interno estandarizado.
- **`HandleDlocalWebhookAction`**: Orquesta la actualización de facturas y suscripciones basada en el resultado del pago (PAID, REJECTED, etc.).

## 4. Capa de Presentación (UI)

### 4.1 Componentes Livewire
- **`PlanManagement`**: Dashboard para la creación y edición de la oferta comercial.
- **`SubscriptionManagement`**: Interfaz para que los administradores globales gestionen el estado de los clientes.
- **`InvoiceManagement`**: Visualizador de ingresos y estado de facturación.
- **`BillingCrud`**: Componente base que unifica la gestión de planes y suscripciones.

### 4.2 Formularios (`Forms/`)
- Utiliza clases `Livewire\Form` para encapsular la validación y el estado de la creación de planes y suscripciones.

## 5. Seguridad y Autorización

### 5.1 Policies
- **`PlanPolicy`**: Controla quién puede crear, editar o eliminar planes comerciales (`central.plans.manage`).
- **`TenantSubscriptionPolicy`**: Restringe la modificación de suscripciones a administradores de facturación (`central.billing.manage`).
- **`TenantInvoicePolicy`**: Permite la visualización de facturas (`central.billing.view`).

### 5.2 Middleware
- Las rutas administrativas están protegidas por el guard `central` y requieren el permiso correspondiente gestionado por el módulo `AdminAuthorization`.

## 6. Consideraciones de Single-DB
- A diferencia de los módulos de Tenant, los datos de facturación residen en la base de datos global (sin `tenant_id` en la tabla `plans`, pero con `tenant_id` en `tenant_subscriptions` y `tenant_invoices`).
- Se utilizan índices en `tenant_id` dentro de las tablas de suscripciones para facilitar reportes rápidos de ingresos por cliente.
