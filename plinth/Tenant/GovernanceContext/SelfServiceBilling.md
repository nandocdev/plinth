# Tenant Module: Self-Service Billing (Autogestión de Pagos)

## 1. Propósito
El módulo **Self-Service Billing** empodera a los clientes de Plinth para gestionar su propia relación comercial con la plataforma. Desde su panel, el tenant puede visualizar su plan actual, actualizar su suscripción (Upsell), descargar facturas pasadas y configurar sus métodos de pago sin intervención del equipo de soporte central.

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Suscripción
- **`GetTenantBillingOverviewAction`**: Recupera el estado actual de la cuenta, incluyendo el plan activo, fecha de próxima renovación y el uso acumulado contra los límites del plan.
- **`GetAvailableUpgradePlansAction`**: Filtra y presenta planes superiores o complementarios disponibles para el tenant actual.
- **`RequestPlanUpgradeAction`**: Inicia el flujo de cambio de plan, gestionando el prorrateo de pagos y la activación inmediata de nuevas funcionalidades.

### 2.2 Gestión de Pagos
- **`GetCheckoutMethodsForTenantContextAction`**: Configura la pasarela de pagos (ej. dLocal) para el contexto específico del tenant (moneda local, métodos de pago permitidos en su región).
- **`ListTenantInvoicesAction`**: Muestra el historial de cargos realizados al cliente.
- **`RecordTenantInvoiceAction`**: Registra un nuevo intento de cobro o la generación de una factura proforma.

## 3. Capa de Presentación (UI)

### 3.1 Portal de Facturación
- **`BillingDashboard`**: Interfaz unificada donde el cliente ve su consumo y estado financiero.
- **`InvoiceViewer`**: Permite la previsualización y descarga de facturas en formato PDF.
- **`PlanSelector`**: Galería interactiva para la comparación y selección de planes de suscripción.

## 4. Seguridad y Aislamiento
- **Acceso Restringido:** Solo usuarios con roles administrativos o financieros en el tenant pueden acceder a este módulo.
- **Privacidad de Datos:** Los métodos de pago (ej. números de tarjeta) nunca se almacenan en los servidores de Plinth; se delegan a la pasarela mediante tokens seguros.

## 5. Integración con el Contexto Central
Aunque la interfaz es del tenant, este módulo se comunica con las `Actions` del contexto Central para persistir los cambios en las tablas globales de suscripciones y facturación, asegurando la integridad comercial de toda la plataforma.

## 6. Consideraciones Técnicas
- El sistema utiliza webhooks de la pasarela para confirmar pagos exitosos y actualizar el estado de la suscripción del tenant de forma asíncrona.
- Se implementan periodos de gracia (Grace Periods) automáticos ante fallos de pago para evitar la interrupción inmediata del servicio al cliente.
