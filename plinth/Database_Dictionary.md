# Diccionario de Datos: Ecosistema Plinth

Este documento describe las tablas core del sistema. Todos los nombres de tablas y columnas deben seguir la convención `snake_case`.

## 1. Tablas Centrales (Landlord)

### `tenants`
Almacena la información raíz de cada cliente.
- `id` (string/PK): Identificador único (ej. 'google', 'acme').
- `data` (json): Configuraciones personalizadas (branding, settings).
- `status` (enum): 'active', 'suspended', 'pending'.
- `trial_ends_at` (timestamp): Fin del periodo de prueba.

### `domains`
Gestiona los accesos URL.
- `id` (id/PK)
- `domain` (string): URL (ej. 'acme.plinth.com' o 'app.acme.com').
- `tenant_id` (string/FK): Relación con `tenants`.
- `is_custom` (boolean): Si es un dominio propio del cliente.
- `is_verified` (boolean): Estado de validación DNS.

### `plans`
Oferta comercial global.
- `id` (id/PK)
- `name` (string): Nombre del plan (ej. 'Pro', 'Enterprise').
- `slug` (string): Identificador URL.
- `price_cents` (integer): Precio en centavos para evitar errores de redondeo.
- `currency` (string): 'USD', 'EUR', etc.
- `features` (json): Listado de feature flags habilitadas.

### `tenant_subscriptions`
Vínculo entre cliente y plan.
- `id` (id/PK)
- `tenant_id` (string/FK)
- `plan_id` (id/FK)
- `starts_at` (timestamp)
- `ends_at` (timestamp)
- `status` (string): 'active', 'past_due', 'canceled'.

## 2. Tablas Multi-tenant (Single-DB)

**Nota:** Todas estas tablas comparten la base de datos única y requieren filtrado por `tenant_id`.

### `users`
- `id` (id/PK)
- `tenant_id` (string/FK): **Obligatorio para usuarios de clientes.**
- `name` (string)
- `email` (string): Único por `tenant_id`.
- `password` (string)
- `two_factor_secret` (text/nullable)
- `status` (string): 'active', 'inactive'.

### `activity_log`
- `id` (id/PK)
- `tenant_id` (string/FK/nullable): Nulo para acciones de Central.
- `log_name` (string): Categoría del log.
- `description` (text): Acción realizada.
- `subject_id` / `subject_type`: Polimorfismo del objeto afectado.
- `causer_id` / `causer_type`: Quién realizó la acción.
- `properties` (json): Datos del request, IP, User Agent y Payloads.

### `tenant_invoices`
- `id` (id/PK)
- `tenant_id` (string/FK)
- `amount_cents` (integer)
- `currency` (string)
- `status` (enum): 'pending', 'paid', 'failed'.
- `external_reference` (string): ID de transacción en la pasarela (dLocal).

## 3. Convenciones Generales
- **Montos Dinero:** Siempre en enteros (`cents`) para evitar problemas de precisión decimal.
- **Fechas:** Siempre `timestamps` con zona horaria UTC.
- **Booleans:** Prefijo `is_` o `has_` (ej. `is_active`, `has_api_access`).
- **Soft Deletes:** Usa la columna `deleted_at` en tablas críticas (Users, Tenants, Plans).
