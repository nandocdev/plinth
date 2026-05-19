# PRD: Plinth Multitenant Ecosystem (Single-DB)

## 1. Visión General
**Plinth** es una plataforma B2B SaaS de alto rendimiento diseñada para ofrecer una experiencia multi-tenencia escalable, robusta y eficiente. El sistema permite la gestión centralizada de múltiples clientes (tenants) bajo una arquitectura de **Base de Datos Única (Single-DB)**, garantizando el aislamiento de datos mediante filtrado lógico a nivel de aplicación.

El ecosistema está construido sobre **Laravel 13**, **Livewire 4** y **Flux**, optimizado para desarrollos rápidos sin sacrificar la mantenibilidad.

## 2. Objetivos del Proyecto
- **Escalabilidad Operativa:** Reducir la sobrecarga de mantenimiento de base de datos eliminando la necesidad de migraciones por cada tenant.
- **Eficiencia de Recursos:** Optimizar el uso de conexiones a la base de datos y memoria del servidor.
- **Aislamiento Seguro:** Implementar un sistema de seguridad de datos inquebrantable basado en `tenant_id` y Global Scopes de Eloquent.
- **Experiencia de Usuario Premium:** Proporcionar interfaces reactivas y modernas mediante Livewire y Flux.

## 3. Arquitectura Técnica
### 3.1 Estrategia de Tenencia: Single-DB
- **Motor de Tenencia:** `stancl/tenancy` configurado para aislamiento lógico.
- **Identificador Único:** Cada registro perteneciente a un tenant debe contener una columna `tenant_id`.
- **Global Scopes:** Uso obligatorio del trait `BelongsToTenant` en todos los modelos del contexto Tenant.
- **Conexión:** Una única base de datos para la aplicación central y todos los datos de los clientes.

### 3.2 Stack Tecnológico
- **Lenguaje:** PHP 8.4+
- **Framework:** Laravel 13.x
- **Frontend:** Livewire 4.x, Flux UI, Tailwind CSS.
- **Base de Datos:** MySQL 8.0+ / PostgreSQL (Single-DB).
- **Caché/Queue:** Redis.
- **Testing:** Pest PHP.
- **Estilo de Código:** Laravel Pint.


### 3.3 Librerías y Dependencias Clave 
| Librería | Propósito y Utilización en Plinth | 
| :--- | :--- | 
| `stancl/tenancy` | Motor core de multi-tenencia. Gestiona la identificación de tenants y la aplicación de Scopes para Single-DB. | 
| `nandocdev/plinth-multitenant-billing` | Paquete propietario para la gestión de facturación, suscripciones y planes en entornos multi-tenant. | 
| `spatie/laravel-permission` | Gestión de Roles y Permisos (RBAC). Utilizado en el `IdentityContext` tanto para Central como para Tenants. | 
| `spatie/laravel-activitylog` | Auditoría de acciones. Configurado para registrar logs vinculados al `tenant_id` en el esquema Single-DB. | 
| `spatie/laravel-medialibrary` | Gestión de archivos y adjuntos. Maneja la persistencia de logos y documentos por tenant. | 
| `livewire/flux` | Librería de componentes UI de alto nivel para interfaces reactivas y consistentes. | 
| `laravel/fortify` | Motor de autenticación headless que soporta el login, registro y 2FA en Central y Tenants. | 
| `laravel/sanctum` | Emisión de API Tokens autenticados, esenciales para el módulo de `ApiAccess`. | 
| `laravel/horizon` | Panel de monitoreo para las colas de Redis, optimizado para procesar tareas asíncronas de múltiples tenants. | 
| `spatie/laravel-backup` | Estrategia de respaldos automáticos para la base de datos única y el almacenamiento. | 
| `laravel/pennant` | Gestión de Feature Flags para habilitar funcionalidades dinámicamente según el tenant o plan. | 
| `laravel/pulse` | Monitoreo de rendimiento en tiempo real para identificar cuellos de botella en el entorno compartido. |
## 4. Estructura de Contextos (Bounded Contexts)

### 4.1 Central Context
Responsable de la administración global del ecosistema y el ciclo de vida de los tenants.
- **Tenant Provisioning:** Creación y gestión de cuentas de clientes.
- **Billing & Subscriptions:** Gestión de planes, pagos y facturación centralizada.
- **Affiliate & Partners:** Sistema de referidos e integraciones externas (webhooks).
- **System Health:** Monitoreo del estado de la plataforma.

### 4.2 Tenant Context
Lógica de negocio específica para cada cliente, aislada lógicamente.
- **Identity Context:** Autenticación, RBAC (Spatie), Impersonación y API Access.
- **Operations Context:** Notificaciones, Webhooks salientes, Logs de actividad y Reporting.
- **Governance Context:** Configuración de marca, dominios personalizados, Feature Flags y Addons.
- **Platform Context:** Espacios de trabajo, gestión de archivos y exportación de datos.

### 4.3 Shared Context
Lógica transversal reutilizable por los contextos Central y Tenant.
- Helpers, Infrastructure (Providers base), y componentes de soporte.

## 5. Requerimientos Funcionales Críticos
1. **Aislamiento de Datos:** Ningún tenant debe poder acceder a los datos de otro bajo ninguna circunstancia.
2. **Personalización (White-label):** Capacidad de cambiar colores, logos y dominios por cada tenant.
3. **Gestión de Planes:** Los módulos y funcionalidades deben activarse/desactivarse según el plan contratado.
4. **Audit Trail:** Registro completo de acciones realizadas por usuarios, filtrable por tenant.

## 6. Requerimientos No Funcionales
- **Rendimiento:** Las consultas deben estar optimizadas con índices compuestos `[tenant_id, ...]` para evitar degradación.
- **Seguridad:** Implementación estricta de Políticas (Laravel Policies) en cada controlador/acción.
- **Mantenibilidad:** Seguir estrictamente las guías de `GEMINI.md` (Services para lógica, Livewire para UI).

## 7. Roadmap de Migración (Hacia Single-DB)
1. **Unificación de Migraciones:** Mover migraciones de `tenant/` a la raíz y añadir `tenant_id`.
2. **Refactorización de Modelos:** Aplicar `BelongsToTenant` a todos los modelos relevantes.
3. **Auditoría de Consultas:** Verificar que no se use `DB::table` sin el filtro manual de `tenant_id`.
4. **Pruebas de Estrés:** Validar el comportamiento con grandes volúmenes de datos en una sola tabla.

---
**Última actualización:** 17 de mayo de 2026
**Estado:** Sprint 1 - Definición de Estructura y PRD.
