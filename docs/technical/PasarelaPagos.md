# Pasarela de pagos para este proyecto (propuesta base)

## Objetivo

Definir una estrategia de pagos multi-provider compatible con la arquitectura actual del repo, sin romper el dominio ya implementado en Central Billing.

Esta propuesta prioriza:

- Continuidad con `TenantSubscription` y `TenantInvoice` existentes.
- Idempotencia y trazabilidad de eventos.
- Enrutamiento de provider por contexto (pais, metodo, tipo de cobro).
- Implementacion incremental por fases.

## Estado actual del proyecto (base real)

- El dominio de billing vive en `app/Central/BillingModule`.
- Ya existen suscripciones y facturas centrales:
    - `TenantSubscription`
    - `TenantInvoice`
- Ya existe procesamiento idempotente de webhooks en `processed_webhooks`.
- Ya existe flujo webhook para dLocal (`central.billing.webhooks.dlocal`).

Conclusion: no se parte de cero. La propuesta debe extender lo actual, no reemplazarlo.

## Principios de diseno

1. No crear un motor de billing paralelo.
2. Mantener una sola fuente de verdad de estado de cobro en backend.
3. Persistir la decision de provider por intento de pago.
4. Evitar reglas "inteligentes" dinamicas al inicio; usar reglas hardcode versionadas.
5. Toda decision de pagos ocurre en Central, no en contexto Tenant.

## Alcance funcional por tipo de cobro

### Suscripciones

- Provider por defecto y obligatorio en MVP: Stripe.
- Debe sincronizar estado en `TenantSubscription`.
- Los cambios de ciclo (`monthly`/`yearly`) siguen usando el dominio ya existente.

### Pagos one-off (si aplica al producto)

- Provider segun resolucion de contexto.
- Deben terminar reflejados en `TenantInvoice` o en una entidad de intento de pago enlazada.

## Arquitectura objetivo (sin sobreingenieria)

### 1) Orquestador en Central Billing

Responsable de:

- Resolver provider.
- Crear intento de pago.
- Delegar a driver provider.
- Persistir decision y respuesta inicial.

Contrato funcional v1:

- Entrada: tenant, request de pago validado y contexto de checkout.
- Salida: resultado normalizado con estado inicial, provider elegido y referencia externa.
- Sin side effects fuera de billing central: no actualiza acceso final del tenant en este paso.

Secuencia obligatoria:

1. Validar payload de entrada y precondiciones de negocio.
2. Resolver provider con reglas versionadas.
3. Abrir transaccion en central y crear payment_attempt en estado created.
4. Invocar driver del provider elegido.
5. Persistir provider_reference, estado inicial y metadata tecnica.
6. Retornar respuesta normalizada para UI/API.

Reglas de consistencia:

- La decision de provider queda congelada en el payment_attempt.
- Si falla la llamada al provider, el intento pasa a failed con motivo trazable.
- Si el provider responde pending, no se concede acceso ni se marca invoice como pagada.
- El estado final solo lo define webhook normalizado o reconciliacion backend.

Idempotencia minima del orquestador:

- Debe soportar request_key/idempotency_key por tenant.
- Reintento con misma key debe devolver el mismo payment_attempt sin duplicar cobro.
- La key se guarda en metadata o columna dedicada con indice unico por tenant.

Errores esperados y manejo:

- Provider no disponible -> fallback solo si la regla lo permite; si no, failed.
- Timeout en provider -> estado pending_review y job de reconciliacion.
- Inconsistencia de moneda/metodo -> rechazo temprano antes de invocar driver.

Observabilidad del orquestador:

- Log estructurado por intento: tenant_id, provider, payment_type, status.
- Metricas: create_success_rate, create_failure_rate, latency_p95 por provider.
- Correlation id por solicitud para seguimiento end to end.

Criterios de aceptacion de esta tarea:

1. Todo intento de pago crea trazabilidad en central.
2. Ningun cobro se dispara sin provider resuelto y persistido.
3. Reintentos no generan cobros duplicados.
4. El contrato de salida del orquestador es unico para todos los drivers.
5. El orquestador no mezcla logica de webhook ni logica de UI.

### 2) Resolucion de provider (v1)

Reglas iniciales sugeridas:

1. Si `type = subscription` -> `stripe`.
2. Si pais del tenant es LATAM y metodo alternativo habilitado -> `mercadopago`.
3. Si metodo solicitado es wallet -> `paypal`.
4. Fallback -> `stripe`.

Importante:

- La resolucion se calcula una sola vez por intento y se persiste.
- No se recalcula luego por cambios de IP, VPN o frontend.

### 3) Drivers por provider

- Stripe driver: suscripciones + tarjeta global.
- Mercado Pago driver: cash/transfer/cuotas (fase LATAM).
- PayPal driver: wallet/fallback.

Cada driver debe exponer un contrato comun de salida para simplificar el orquestador.

### 4) Normalizacion de webhooks

Objetivo:

- Convertir payload propietario a evento canonico interno.

Evento canonico minimo:

- `provider`
- `provider_event_id`
- `event_type`
- `tenant_id`
- `payment_reference` (si existe)
- `occurred_at`
- `raw_payload`

Reglas:

- Idempotencia por (`provider`, `provider_event_id`).
- Procesamiento transaccional.
- Solo backend actualiza estado final de pago/suscripcion.

## Modelo de datos propuesto (compatible con lo actual)

No duplicar `subscriptions` ni `invoices`.

Extender con entidades minimas:

### `payment_attempts` (nueva)

- `id`
- `tenant_id`
- `provider`
- `payment_type` (`subscription`, `one_off`)
- `method_type` (`card`, `cash`, `wallet`, etc.)
- `country_context`
- `currency`
- `amount_cents`
- `status` (`created`, `pending`, `succeeded`, `failed`, `expired`, `canceled`)
- `provider_reference`
- `resolution_version`
- `metadata` (json)
- `created_at`, `updated_at`

### `payment_provider_events` (nueva o reutilizando processed_webhooks)

Si se reutiliza `processed_webhooks`, extender para soportar multiples providers sin ambiguedad.

Campos clave:

- `provider`
- `provider_event_id`
- `payload_hash`
- `processed_at`

Indice unico recomendado:

- (`provider`, `provider_event_id`)

## Ubicacion en el proyecto (modular)

Implementacion en `app/Central/BillingModule`:

- `Actions/`
    - `ResolvePaymentProviderAction`
    - `CreatePaymentAttemptAction`
    - `HandleProviderWebhookAction`
    - `MarkPaymentAttemptStatusAction`
- `Services/`
    - `PaymentOrchestratorService`
- `Contracts/` (en Shared si se reutiliza)
    - `PaymentProviderDriver`
- `DTOs/`
    - `PaymentRequestDTO`
    - `ProviderWebhookEventDTO`
- `Models/`
    - `PaymentAttempt`
    - entidad de eventos procesados (nueva o adaptada)
- `Routes/`
    - endpoints webhook por provider dentro de rutas centrales de billing

## Flujo de checkout (producto)

No mostrar todos los metodos a todos los usuarios.

Regla de UX:

- LATAM: tarjeta, metodos locales (MP), wallet.
- Resto del mundo: tarjeta, wallet.

La UI sugiere, pero la seleccion final valida en backend con reglas de orquestador.

## Plan de rollout por fases

### Fase 1: Base estable

- Consolidar Stripe para suscripciones.
- Introducir `payment_attempts` para trazabilidad.
- Homologar idempotencia de webhooks.
- Dashboard tecnico minimo de conversion/fallos por provider.

### Fase 2: Cobertura LATAM

- Integrar Mercado Pago.
- Habilitar metodos locales segun pais/contexto.
- Manejar estados pendientes y expiracion de pagos offline.

### Fase 3: Optimizacion conversion

- Integrar PayPal como opcion secundaria.
- Ajustar orden y visibilidad de metodos por conversion observada.

## Edge cases obligatorios

1. Pago iniciado y no completado (estado zombie) -> expira y reconcilia.
2. Webhook duplicado -> no reprocesar.
3. Webhook tardio/out-of-order -> respetar version de estado y timestamps.
4. Provider confirma pero falla post-proceso interno -> retry seguro e idempotente.
5. Cambio de pais/IP entre inicio y confirmacion -> mantener provider decidido en el intento original.

## Observabilidad y metricas minimas

- Conversion rate por provider.
- Failure rate por provider y metodo.
- Tiempo medio de confirmacion.
- Ratio de pagos pendientes/expirados.
- Reintentos webhook y tasa de duplicados.

## No hacer (guardrails)

- No depender del frontend para definir estado final.
- No cambiar provider despues de crear intento sin registrar nueva version.
- No crear una tabla de suscripciones paralela.
- No mezclar datos tenant de pagos en DB tenant (billing central debe seguir centralizado).

## Criterios de aceptacion de la propuesta

1. La propuesta no rompe `TenantSubscription` ni `TenantInvoice`.
2. El flujo webhook queda idempotente por provider+event_id.
3. Existe trazabilidad de decision de provider por intento.
4. El rollout puede ejecutarse por fases sin reescribir dominio.
5. Se preserva la separacion Central/Tenant del proyecto.

## Decision ejecutiva

- Stripe permanece como columna vertebral de suscripciones.
- Mercado Pago y PayPal se incorporan de forma incremental para cobertura y conversion.
- La ventaja del producto se concentra en el orquestador y la disciplina operativa, no en el provider.


---

## ROADMAP DE IMPLEMENTACION

### 0) Preparacion tecnica y alineacion

- [x] Confirmar rama base de feature para pagos (derivaciones posteriores por modulo/fase).
- [x] Validar versionado real del proyecto (Laravel/PHP) vs documentos de proyecto.
- [x] Cerrar decision oficial de libreria base para suscripciones (Cashier) y documentarla en docs/project.
- [x] Definir alcance MVP exacto: suscripciones + one-off habilitado o postergado.
- [x] Acordar naming final de entidades nuevas (`payment_attempts`, `payment_provider_events`).
- [x] Acordar formato de `idempotency_key` y politica de expiracion/reuso.
- [x] Acordar contrato canonico de eventos webhook multi-provider.

Acuerdos cerrados de la fase 0:

- Rama base confirmada: `feat/payment-gateway-base`.
- Versionado real validado: proyecto corre con PHP 8.4 y Laravel 13; la documentacion heredada referenciaba Laravel 12/PHP 8.3.
- Libreria oficial para suscripciones: `laravel/cashier` como base obligatoria de Fase 1.
- Alcance MVP cerrado: suscripciones con Stripe; pagos one-off quedan habilitados a nivel de dominio pero se activan despues de estabilizar Fase 1.
- Naming aprobado: `payment_attempts` y `payment_provider_events`.
- Formato de idempotencia aprobado: `idempotency_key` por tenant + operacion de negocio, con unicidad por tenant y ventana de reuso controlada por expiracion.
- Contrato canonico webhook aprobado con campos minimos: `provider`, `provider_event_id`, `event_type`, `tenant_id`, `payment_reference`, `occurred_at`, `raw_payload`.

### 1) Base de dominio (Central Billing)

- [x] Crear ADR interno para "Orquestador de Pagos v1" (decision y limites).
- [x] Definir DTOs de entrada/salida del orquestador.
- [x] Definir contrato `PaymentProviderDriver` en Shared o Central (segun reutilizacion real).
- [x] Definir `PaymentOrchestratorService` con flujo estricto (resolver -> intento -> driver -> persistir).
- [x] Definir accion de resolucion de provider versionada (`ResolvePaymentProviderAction`).
- [x] Definir reglas de fallback permitidas por tipo de cobro.
- [x] Definir codigos de error normalizados para UI/API.

Acuerdos cerrados de la seccion 1:

- ADR registrado: `docs/technical/adr/ADR-001-payment-orchestrator-v1.md`.
- DTOs base aprobados:
    - `PaymentRequestDTO`
    - `PaymentOrchestratorResultDTO`
    - `ProviderWebhookEventDTO`
- Contrato de providers aprobado: `PaymentProviderDriver` (ubicacion objetivo: Shared Contracts, implementaciones en Central Billing).
- Servicio de dominio aprobado: `PaymentOrchestratorService` con flujo inmutable por intento.
- Action de resolucion aprobada: `ResolvePaymentProviderAction` (reglas versionadas).
- Reglas de fallback aprobadas:
    - `subscription` solo Stripe.
    - `wallet` con fallback controlado a PayPal.
    - Sin cambio de provider luego de crear el intento.
- Catalogo de errores v1 aprobado:
    - `PAYMENT_PROVIDER_UNAVAILABLE`
    - `PAYMENT_METHOD_NOT_ALLOWED`
    - `PAYMENT_CURRENCY_NOT_SUPPORTED`
    - `PAYMENT_REQUEST_INVALID`
    - `PAYMENT_TIMEOUT`
    - `PAYMENT_IDEMPOTENCY_CONFLICT`

Implementacion base creada (rama de trabajo actual):

- DTOs compartidos:
    - `app/Shared/DTOs/Payments/PaymentRequestDTO.php`
    - `app/Shared/DTOs/Payments/PaymentProviderResultDTO.php`
    - `app/Shared/DTOs/Payments/PaymentOrchestratorResultDTO.php`
    - `app/Shared/DTOs/Payments/ProviderWebhookEventDTO.php`
- Contratos:
    - `app/Shared/Contracts/Payments/PaymentProviderDriver.php`
    - `app/Central/BillingModule/Contracts/PaymentAttemptStore.php`
- Dominio Central Billing:
    - `app/Central/BillingModule/Actions/ResolvePaymentProviderAction.php`
    - `app/Central/BillingModule/Services/PaymentOrchestratorService.php`
    - `app/Central/BillingModule/Enums/PaymentErrorCode.php`

### 2) Datos e infraestructura de persistencia

- [x] Diseñar migracion central para `payment_attempts` con constraints e indices.
- [x] Agregar indice unico para idempotencia por tenant + request key.
- [x] Diseñar migracion para eventos de provider (o extender `processed_webhooks` sin ambiguedad).
- [x] Agregar indice unico por `provider + provider_event_id`.
- [x] Definir politica de retencion de `raw_payload` y metadata tecnica.
- [x] Definir estrategia de auditoria de cambios de estado (activity log / eventos).

Acuerdos cerrados de la seccion 2:

- Migracion `payment_attempts` creada con constraints de dominio y indices operativos.
- Idempotencia garantizada por indice unico (`tenant_id`, `idempotency_key`).
- Migracion `payment_provider_events` creada para normalizacion de eventos multi-provider.
- Dedupe de eventos garantizado por indice unico (`provider`, `provider_event_id`).
- Politica de retencion acordada:
    - `raw_payload`: retencion corta (objetivo 90 dias; columna `raw_payload_retention_until`).
    - `metadata`: retencion extendida para trazabilidad operativa (objetivo 12 meses; columna `metadata_retention_until`).
- Estrategia de auditoria acordada:
    - Auditoria de cambios de estado via eventos de dominio + activity log central.
    - `payment_provider_events` conserva evidencia tecnica de entrada/procesamiento.

Implementacion de persistencia creada:

- Migraciones central:
    - `database/migrations/central/2026_04_16_000000_create_payment_attempts_table.php`
    - `database/migrations/central/2026_04_16_000100_create_payment_provider_events_table.php`
- Modelos:
    - `app/Central/BillingModule/Models/PaymentAttempt.php`
    - `app/Central/BillingModule/Models/PaymentProviderEvent.php`
- Store de persistencia:
    - `app/Central/BillingModule/Contracts/PaymentAttemptStore.php`
    - `app/Central/BillingModule/Services/EloquentPaymentAttemptStore.php`
    - binding en `app/Central/BillingModule/Providers/BillingModuleServiceProvider.php`

### 3) Fase 1 - Stripe estable (suscripciones)

- [x] Implementar driver Stripe con contrato comun.
- [x] Conectar orquestador a Stripe para `payment_type=subscription`.
- [x] Persistir decision de provider y `provider_reference` en cada intento.
- [x] Normalizar estados iniciales (`created`, `pending`, `failed`, `succeeded`).
- [x] Alinear webhook de Stripe al pipeline canonico de eventos.
- [x] Garantizar idempotencia de webhook en transaccion.
- [x] Sincronizar estado final en `TenantSubscription` sin crear tablas paralelas.
- [x] Verificar que `TenantInvoice` se actualiza/relaciona sin romper flujo actual.

Acuerdos cerrados de la seccion 3:

- Driver Stripe implementado con contrato `SubscriptionPaymentProviderDriver` y salida normalizada.
- Creacion de suscripcion central integrada con Stripe en `CreateSubscriptionAction`, persistiendo `external_id` como `provider_reference`.
- Webhook Stripe agregado a rutas centrales y pipeline canonico (verify -> normalize -> handle).
- Idempotencia de webhook garantizada por `processed_webhooks` (`provider`, `event_id`) dentro de transaccion central.
- Sincronizacion de estados Stripe hacia `TenantSubscription` respetando transiciones validas.
- Sincronizacion de facturas Stripe hacia `TenantInvoice` sin introducir tablas paralelas.

Implementacion Stripe creada:

- Contrato + driver:
    - `app/Central/BillingModule/Contracts/SubscriptionPaymentProviderDriver.php`
    - `app/Central/BillingModule/Services/StripeSubscriptionDriver.php`
- Integracion de suscripcion:
    - `app/Central/BillingModule/Actions/CreateStripeSubscriptionAction.php`
    - `app/Central/BillingModule/Actions/CreateSubscriptionAction.php`
- Webhook Stripe:
    - `app/Central/BillingModule/Actions/VerifyStripeWebhookSignatureAction.php`
    - `app/Central/BillingModule/Actions/NormalizeStripeWebhookAction.php`
    - `app/Central/BillingModule/Actions/HandleStripeWebhookAction.php`
    - `app/Central/BillingModule/Http/Controllers/StripeWebhookController.php`
    - `app/Central/BillingModule/Routes/web.php`
- Soporte de configuracion:
    - `config/services.php` (`services.stripe.*`)

### 4) Fase 1 - Observabilidad y operacion

- [x] Emitir log estructurado por intento (tenant, provider, type, status, correlation_id).
- [x] Exponer metricas minimas por provider (success/failure/latency).
- [x] Agregar panel tecnico minimo en central para intents y fallos.
- [x] Agregar alertas para picos de fallos por provider.
- [x] Definir runbook operativo para incidentes de pagos.

Acuerdos cerrados de la seccion 4:

- Logs estructurados activos en `subscription.create` y `webhook.process` con `correlation_id`.
- Metricas por provider persistidas en `payment_operation_events` (success/failure/pending + latencia).
- Panel tecnico central habilitado en `central/billing/observability`.
- Alertas de pico de fallos persistidas en `payment_operation_alerts` con umbral configurable.
- Runbook operativo documentado para triage/contencion/recuperacion.

Implementacion de observabilidad creada:

- Persistencia:
    - `database/migrations/central/2026_04_16_010000_create_payment_operation_events_table.php`
    - `database/migrations/central/2026_04_16_010100_create_payment_operation_alerts_table.php`
    - `app/Central/BillingModule/Models/PaymentOperationEvent.php`
    - `app/Central/BillingModule/Models/PaymentOperationAlert.php`
- Dominio observabilidad:
    - `app/Central/BillingModule/DTOs/RecordPaymentOperationData.php`
    - `app/Central/BillingModule/Actions/RecordPaymentOperationAction.php`
    - `app/Central/BillingModule/Actions/DetectPaymentFailureSpikeAction.php`
    - `app/Central/BillingModule/Actions/GetPaymentObservabilitySnapshotAction.php`
- Panel central:
    - `app/Central/BillingModule/Livewire/PaymentObservabilityDashboard.php`
    - `app/Central/BillingModule/Resources/Views/livewire/payment-observability-dashboard.blade.php`
    - `app/Central/BillingModule/Routes/web.php` (`central.billing.observability`)
- Instrumentacion de flujos:
    - `app/Central/BillingModule/Actions/CreateSubscriptionAction.php`
    - `app/Central/BillingModule/Actions/HandleDlocalWebhookAction.php`
- Configuracion:
    - `config/billing.php`
- Operacion:
    - `docs/technical/runbooks/payment-incidents.md`

### 5) Fase 1 - QA y seguridad

- [x] Tests unitarios de resolucion de provider (reglas + fallback).
- [x] Tests unitarios de idempotencia de orquestador.
- [x] Tests feature de webhooks duplicados y out-of-order.
- [x] Tests de regresion de `TenantSubscription` y `TenantInvoice`.
- [x] Tests de aislamiento (sin fuga entre tenants en central billing).
- [x] Validar autorizacion/policies en flujos UI de billing central.
- [x] Validar reintentos seguros en jobs de reconciliacion.

Acuerdos cerrados de la seccion 5:

- Cobertura unitaria agregada para resolucion de provider y fallback.
- Cobertura unitaria agregada para idempotencia del orquestador.
- Cobertura feature reforzada para webhooks duplicados y out-of-order.
- Cobertura de regresion agregada sobre consistencia `TenantSubscription` / `TenantInvoice`.
- Cobertura de aislamiento agregada para evitar fugas cross-tenant en webhook billing central.
- Cobertura de seguridad agregada para autorizacion en UI central de billing.
- Job de reconciliacion implementado y validado como retry-safe e idempotente.

Implementacion QA y seguridad creada:

- Dominio QA:
    - `app/Central/BillingModule/DTOs/PaymentProviderResolutionData.php`
    - `app/Central/BillingModule/Actions/ResolvePaymentProviderAction.php`
    - `app/Central/BillingModule/Services/PaymentOrchestratorService.php`
- Reconciliacion:
    - `app/Central/BillingModule/Actions/ReconcileSubscriptionStatusAction.php`
    - `app/Central/BillingModule/Jobs/ReconcileSubscriptionStatusJob.php`
- Tests unitarios:
    - `tests/Unit/Central/BillingModule/ResolvePaymentProviderActionTest.php`
    - `tests/Unit/Central/BillingModule/PaymentOrchestratorServiceTest.php`
- Tests feature:
    - `tests/Feature/Central/BillingModule/BillingAuthorizationPolicyTest.php`
    - `tests/Feature/Central/BillingModule/BillingRegressionAndIsolationTest.php`
    - `tests/Feature/Central/BillingModule/BillingWebhookAndLifecycleQaTest.php`

### 6) Fase 2 - Cobertura LATAM (Mercado Pago)

- [x] Confirmar decision de SDK oficial vs integracion HTTP adapter.
- [x] Implementar driver Mercado Pago con contrato comun.
- [x] Habilitar metodos locales por pais/contexto.
- [x] Modelar estados asincronos de pago offline (`pending`, `expired`).
- [x] Implementar reconciliacion de pagos pendientes y zombies.
- [x] Integrar webhook/notification MP al esquema canonico idempotente.
- [x] Agregar metricas comparativas Stripe vs MP.

Acuerdos cerrados de la seccion 6:

- Decision tecnica oficial: integracion por HTTP adapter usando cliente HTTP nativo de Laravel para reducir acoplamiento y facilitar observabilidad/reintentos.
- Contrato comun de drivers definido en `PaymentProviderDriver`; implementacion inicial activa para Mercado Pago.
- Matriz de metodos LATAM activada por pais (`BR`, `AR`, `MX`, resto LATAM) con fallback controlado fuera de LATAM.
- Estados asincronos offline modelados en `payment_attempts` con checks de dominio (`pending`, `expired`, `pending_review`, etc.).
- Reconciliacion automatica de zombies implementada en Action + Job unico para reintentos seguros.
- Webhook Mercado Pago integrado al pipeline canonico (verify -> normalize -> handle) con idempotencia por `provider + event_id`.
- Metricas comparativas Stripe vs Mercado Pago disponibles via accion agregada de conversion/estado.

Implementacion LATAM creada:

- Persistencia:
    - `database/migrations/central/2026_04_16_020000_create_payment_attempts_table.php`
    - `app/Central/BillingModule/Models/PaymentAttempt.php`
- Contratos y driver:
    - `app/Central/BillingModule/Contracts/PaymentProviderDriver.php`
    - `app/Central/BillingModule/Services/MercadoPagoPaymentDriver.php`
- Orquestacion / intents:
    - `app/Central/BillingModule/DTOs/CreatePaymentAttemptData.php`
    - `app/Central/BillingModule/Actions/CreatePaymentAttemptAction.php`
    - `app/Central/BillingModule/Actions/ResolveLatamPaymentMethodsAction.php`
    - `app/Central/BillingModule/Actions/InitiateMercadoPagoSubscriptionPaymentAction.php`
- Webhook Mercado Pago:
    - `app/Central/BillingModule/DTOs/MercadoPagoWebhookData.php`
    - `app/Central/BillingModule/Actions/VerifyMercadoPagoWebhookSignatureAction.php`
    - `app/Central/BillingModule/Actions/NormalizeMercadoPagoWebhookAction.php`
    - `app/Central/BillingModule/Actions/HandleMercadoPagoWebhookAction.php`
    - `app/Central/BillingModule/Http/Controllers/MercadoPagoWebhookController.php`
    - `app/Central/BillingModule/Routes/web.php`
- Reconciliacion y metricas:
    - `app/Central/BillingModule/Actions/ReconcilePendingMercadoPagoAttemptsAction.php`
    - `app/Central/BillingModule/Jobs/ReconcilePendingMercadoPagoAttemptsJob.php`
    - `app/Central/BillingModule/Actions/GetPaymentProviderComparisonMetricsAction.php`
- Configuracion:
    - `config/services.php` (`services.mercadopago.*`)
- Tests:
    - `tests/Feature/Central/BillingModule/MercadoPagoWebhookTest.php`
    - `tests/Feature/Central/BillingModule/MercadoPagoReconciliationAndMetricsTest.php`

### 7) Fase 3 - Wallet/fallback (PayPal)

- [x] Confirmar alcance de PayPal (solo wallet/fallback).
- [x] Implementar driver PayPal con contrato comun.
- [x] Integrar eventos de PayPal al pipeline webhook canonico.
- [x] Ajustar regla de resolucion para wallet sin romper persistencia.
- [x] Medir impacto de conversion y fallo por metodo wallet.

Acuerdos cerrados de la seccion 7:

- Alcance oficial cerrado: PayPal se habilita solo para `method_type=wallet` y escenarios de fallback controlado.
- Driver `PayPalPaymentDriver` integrado bajo contrato comun `PaymentProviderDriver` para mantener salida uniforme por provider.
- Webhook PayPal conectado al pipeline canonico (verify -> normalize -> handle) reutilizando dedupe por `processed_webhooks`.
- Regla de resolucion ajustada en `ResolvePaymentProviderAction`: wallet prioriza PayPal sin alterar regla estricta de suscripciones en Stripe.
- Metricas wallet añadidas para medir conversion/fallo por provider usando `GetWalletMethodImpactMetricsAction`.

Implementacion PayPal creada:

- Dominio:
    - `app/Central/BillingModule/DTOs/ResolvePaymentProviderData.php`
    - `app/Central/BillingModule/Actions/ResolvePaymentProviderAction.php`
    - `app/Central/BillingModule/Services/PayPalPaymentDriver.php`
- Webhook PayPal:
    - `app/Central/BillingModule/DTOs/PayPalWebhookData.php`
    - `app/Central/BillingModule/Actions/VerifyPayPalWebhookSignatureAction.php`
    - `app/Central/BillingModule/Actions/NormalizePayPalWebhookAction.php`
    - `app/Central/BillingModule/Actions/HandlePayPalWebhookAction.php`
    - `app/Central/BillingModule/Http/Controllers/PayPalWebhookController.php`
    - `app/Central/BillingModule/Routes/web.php`
- Metricas:
    - `app/Central/BillingModule/Actions/GetWalletMethodImpactMetricsAction.php`
    - `app/Central/BillingModule/Actions/GetPaymentProviderComparisonMetricsAction.php`
- Configuracion:
    - `config/services.php` (`services.paypal.*`)
- Tests:
    - `tests/Feature/Central/BillingModule/PayPalWebhookAndWalletMetricsTest.php`

Acuerdos cerrados de la seccion 7:

- Alcance oficial cerrado: PayPal se habilita solo para `method_type=wallet` y escenarios de fallback controlado.
- Driver `PayPalPaymentDriver` integrado bajo contrato comun `PaymentProviderDriver` para mantener salida uniforme por provider.
- Webhook PayPal conectado al pipeline canonico (verify -> normalize -> handle) reutilizando dedupe por `processed_webhooks`.
- Regla de resolucion ajustada en `ResolvePaymentProviderAction`: wallet prioriza PayPal sin alterar regla estricta de suscripciones en Stripe.
- Metricas wallet añadidas para medir conversion/fallo por provider usando `GetWalletMethodImpactMetricsAction`.

Implementacion PayPal creada:

- Dominio:
    - `app/Central/BillingModule/DTOs/ResolvePaymentProviderData.php`
    - `app/Central/BillingModule/Actions/ResolvePaymentProviderAction.php`
    - `app/Central/BillingModule/Services/PayPalPaymentDriver.php`
- Webhook PayPal:
    - `app/Central/BillingModule/DTOs/PayPalWebhookData.php`
    - `app/Central/BillingModule/Actions/VerifyPayPalWebhookSignatureAction.php`
    - `app/Central/BillingModule/Actions/NormalizePayPalWebhookAction.php`
    - `app/Central/BillingModule/Actions/HandlePayPalWebhookAction.php`
    - `app/Central/BillingModule/Http/Controllers/PayPalWebhookController.php`
    - `app/Central/BillingModule/Routes/web.php`
- Metricas:
    - `app/Central/BillingModule/Actions/GetWalletMethodImpactMetricsAction.php`
    - `app/Central/BillingModule/Actions/GetPaymentProviderComparisonMetricsAction.php`
- Configuracion:
    - `config/services.php` (`services.paypal.*`)
- Tests:
    - `tests/Feature/Central/BillingModule/PayPalWebhookAndWalletMetricsTest.php`

### 8) UX de checkout por contexto

- [x] Definir matriz de metodos visibles por region/pais.
- [x] Implementar orden de metodos configurable por conversion observada.
- [x] Evitar mostrar metodos no disponibles por contexto.
- [x] Garantizar validacion backend de seleccion final (no confiar en frontend).
- [x] Agregar mensajes de estado claros para pagos pendientes/manuales.

Acuerdos cerrados de la seccion 8:

- Matriz de metodos activa por contexto: LATAM muestra `card`, `transfer`, `cash`, `wallet`; resto del mundo muestra `card`, `wallet`.
- El orden de metodos es configurable via `config/checkout.php` con prioridad por conversion observada (`observed_method_order`).
- La UI tenant oculta metodos no habilitados para el contexto actual y expone solo opciones permitidas.
- La validacion final ocurre en backend dentro de `RequestPlanUpgradeAction`; frontend no puede forzar un metodo no permitido.
- Para metodos manuales (`cash`, `transfer`) se exponen mensajes explicitos de estado pendiente/confirmacion.

Implementacion UX checkout creada:

- Configuracion:
    - `config/checkout.php`
- Dominio tenant self-service:
    - `app/Tenant/GovernanceContext/SelfServiceBillingModule/DTOs/CheckoutMethodOptionData.php`
    - `app/Tenant/GovernanceContext/SelfServiceBillingModule/Actions/GetCheckoutMethodsForTenantContextAction.php`
    - `app/Tenant/GovernanceContext/SelfServiceBillingModule/Actions/RequestPlanUpgradeAction.php`
    - `app/Tenant/GovernanceContext/SelfServiceBillingModule/DTOs/RequestPlanUpgradeData.php`
- Livewire + UX:
    - `app/Tenant/GovernanceContext/SelfServiceBillingModule/Livewire/Forms/PlanUpgradeForm.php`
    - `app/Tenant/GovernanceContext/SelfServiceBillingModule/Livewire/TenantBillingPortal.php`
    - `app/Tenant/GovernanceContext/SelfServiceBillingModule/Resources/Views/livewire/tenant-billing-portal.blade.php`
- Tests:
    - `tests/Feature/Tenant/SelfServiceBillingModule/CheckoutMethodsByContextTest.php`

### 9) Hardening de edge cases

- [ ] Expiracion automatica de intents sin confirmacion.
- [ ] Manejo de webhooks tardios sin regresion de estado.
- [ ] Proteccion contra dobles cobros por retry simultaneo.
- [ ] Politica de compensacion cuando provider confirma y backend falla.
- [ ] Trazabilidad completa de cada transicion de estado.

### 10) Documentacion y governance

- [ ] Actualizar docs/project/07_BILLING.md con arquitectura multi-provider acordada.
- [ ] Actualizar docs/project/01_Dependencias_Requeridas.md con librerias efectivas aprobadas.
- [ ] Actualizar docs/technical/features.md con estado por milestone (Implementado/Parcial).
- [ ] Documentar contrato canonico de webhooks por provider.
- [ ] Documentar checklist de despliegue y rollback.

### 11) Cierre de release por fase

- [ ] Ejecutar suite de tests completa (`php artisan test`).
- [ ] Validar lint/estatico (Pint + Larastan).
- [ ] Crear commits atomicos por unidad logica (migraciones, actions, drivers, tests, docs).
- [ ] Abrir PR con matriz de riesgos y plan de rollback.
- [ ] Merge a develop con historial preservado.

### 12) Criterio final de "listo para produccion"

- [ ] Idempotencia end-to-end validada por pruebas y logs.
- [ ] Sin duplicacion de dominio de suscripciones/facturas existentes.
- [ ] Reconciliacion automatica activa para estados pendientes/zombie.
- [ ] Observabilidad operativa suficiente para soporte 24/7.
- [ ] Documentacion actualizada y alineada con codigo real.