# Análisis Arquitectónico y Estructural: Código vs. Documentación `plinth/`

Este documento presenta un análisis exhaustivo y comparativo de la base de código actual del proyecto **Plinth** frente a las especificaciones arquitectónicas definidas en la carpeta de documentación `plinth/`.

---

## 1. Resumen Ejecutivo

Al analizar tanto la estructura del código como las definiciones de diseño de la plataforma, se ha identificado una **alineación estructural impresionante y de altísima calidad**, combinada con una **divergencia arquitectónica crítica en el modelo de tenencia**.

*   **Alineación de Módulos (100% Coincidencia):** La organización de directorios en `app/` es un espejo exacto del diseño conceptual detallado en `plinth/`. Los contextos `Central`, `Tenant` y `Shared`, así como cada uno de los submódulos (ej. `TenantProvisioningModule`, `ApiAccessModule`, `SettingsModule`), están físicamente implementados con una simetría impecable.
*   **Estándares de Código Excepcionales:** El código implementa con rigurosidad los principios descritos en la guía de Fernando Castillo (`GEMINI.md` / `user_global` rules): tipos estrictos (`declare(strict_types=1)`), Actions como clases finales con un único método `execute()`, DTOs inmutables para el paso de datos, y separación estricta entre Livewire (UI/Estado) y Actions (Lógica).
*   **Divergencia de Tenencia (Multi-DB vs. Single-DB):** La documentación en `plinth/` describe un ecosistema multi-tenant de **Base de Datos Única (Single-DB)** basado en aislamiento lógico con la columna `tenant_id` y el trait `BelongsToTenant`. Sin embargo, el código físico está configurado e implementado bajo una estrategia de **Multi-Base de Datos (DB por tenant)**, con conexiones dinámicas separadas y migraciones independientes ejecutadas mediante switching de conexiones en `stancl/tenancy`.

---

## 2. Comparativa de Arquitectura de Datos: Divergencia Principal

La discrepancia más importante reside en cómo se gestionan y se aíslan los datos de los clientes.

| Característica               | Especificación en `plinth/PRD.md`                                                                                     | Implementación Actual en el Código (`app/`, `config/`)                                                                                             |
| :--------------------------- | :-------------------------------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Modelo de Tenencia**       | **Single-DB (Aislamiento Lógico)**                                                                                    | **Multi-DB (Aislamiento Físico)**                                                                                                                  |
| **Bases de Datos**           | Una sola base de datos física compartida para la aplicación central y todos los tenants.                              | Una base de datos central (`plinth_central`) y bases de datos independientes por cada tenant (ej. `tenant_us_east_1_tenantid`).                    |
| **Aislamiento en Modelos**   | Uso obligatorio del trait `BelongsToTenant` para inyectar un Global Scope en todas las consultas del contexto Tenant. | No se utiliza el trait `BelongsToTenant` en los modelos de `app/Tenant/`. El aislamiento se da a nivel de conexión de base de datos activa.        |
| **Estructura de Tablas**     | Cada registro perteneciente a un tenant en tablas comunes debe contener la columna `tenant_id`.                       | Las tablas dentro del contexto Tenant (ej. `users`, `invitations`) se crean en bases de datos separadas y **no contienen** la columna `tenant_id`. |
| **Ubicación de Migraciones** | Unificación de migraciones: todas las tablas residen en la raíz de migraciones y contienen el filtro `tenant_id`.     | Las migraciones están físicamente divididas en `database/migrations/central` y `database/migrations/tenant`.                                       |
| **Gestión de Conexión**      | No hay switching de base de datos, solo aplicación de filtros dinámicos basados en el tenant inicializado.            | Utiliza `DatabaseTenancyBootstrapper` para cambiar dinámicamente la conexión PDO activa a la base de datos del tenant al inicializar la sesión.    |

> [!IMPORTANT]
> **Modelo Híbrido Detectado:**
> A pesar de que la base está estructurada para Multi-DB, existen algunas tablas asociadas al tenant que se definieron físicamente en las migraciones de `central` con la columna `tenant_id` (ej. `tenant_settings`, `tenant_uploaded_files`, `tenant_csv_transfer_runs`). Esto representa un estado intermedio o una arquitectura híbrida donde ciertos metadatos del tenant residen en la base de datos global y los datos operacionales de su workspace residen en su base de datos física aislada.

---

## 3. Mapeo y Cohesión de Contextos

La base de código sigue un patrón modular extraordinario. A continuación se detalla cómo mapea cada archivo de documentación con los directorios reales de la aplicación:

### 3.1 Contexto Central (`app/Central/` $\leftrightarrow$ `plinth/Central/`)
| Archivo de Documentación               | Módulo Físico Implementado  | Estado del Mapeo                                                        |
| :------------------------------------- | :-------------------------- | :---------------------------------------------------------------------- |
| `plinth/Central/ActivityLog.md`        | `ActivityLogModule/`        | **100% Alineado** (Models, Actions, Views y Livewire presentes)         |
| `plinth/Central/AdminAuthorization.md` | `AdminAuthorizationModule/` | **100% Alineado** (Contiene Roles y Permisos de administración central) |
| `plinth/Central/Affiliate.md`          | `AffiliateModule/`          | **100% Alineado** (Manejo de partners, comisiones y conversiones)       |
| `plinth/Central/Authentication.md`     | `AuthenticationModule/`     | **100% Alineado** (Autenticación Fortify / 2FA para Central)            |
| `plinth/Central/Billing.md`            | `BillingModule/`            | **100% Alineado** (Facturas, Webhooks dLocal y Planes)                  |
| `plinth/Central/DataExport.md`         | `DataExportModule/`         | **100% Alineado** (Exportación global de datos)                         |
| `plinth/Central/Notification.md`       | `NotificationModule/`       | **100% Alineado** (Notificaciones por email/SMS a nivel plataforma)     |
| `plinth/Central/PartnerWebhook.md`     | `PartnerWebhookModule/`     | **100% Alineado** (Envío de webhooks a integraciones de partners)       |
| `plinth/Central/SystemHealth.md`       | `SystemHealthModule/`       | **100% Alineado** (Monitoreo de estado del sistema)                     |
| `plinth/Central/TenantProvisioning.md` | `TenantProvisioningModule/` | **100% Alineado** (Provisioning Actions, OTT Impersonation, Dominios)   |

### 3.2 Contexto Shared (`app/Shared/` $\leftrightarrow$ `plinth/Shared/`)
| Archivo de Documentación          | Carpeta Física Implementada | Estado del Mapeo                                                      |
| :-------------------------------- | :-------------------------- | :-------------------------------------------------------------------- |
| `plinth/Shared/Helpers.md`        | `Helpers/`                  | **100% Alineado** (Contiene `TenantSidebarMenuHelper`)                |
| `plinth/Shared/Infrastructure.md` | `Infrastructure/`           | **100% Alineado** (Providers base compartidos)                        |
| `plinth/Shared/Support.md`        | `Support/`                  | **100% Alineado** (Contiene reglas de validación comunes y catálogos) |

### 3.3 Contexto Tenant (`app/Tenant/` $\leftrightarrow$ `plinth/Tenant/`)
El contexto Tenant está dividido en 4 Bounded Contexts operacionales:

#### A. Governance Context (`app/Tenant/GovernanceContext/`)
*   `Addons.md` $\rightarrow$ `AddonsModule/`
*   `CustomDomain.md` $\rightarrow$ `CustomDomainModule/`
*   `FeatureFlags.md` $\rightarrow$ `FeatureFlagsModule/`
*   `LandingBuilder.md` $\rightarrow$ `LandingBuilderModule/`
*   `SelfServiceBilling.md` $\rightarrow$ `SelfServiceBillingModule/`
*   `Settings.md` $\dots$ `SettingsModule/`

#### B. Identity Context (`app/Tenant/IdentityContext/`)
*   `ApiAccess.md` $\rightarrow$ `ApiAccessModule/`
*   `Authentication.md` $\rightarrow$ `AuthenticationModule/`
*   `Authorization.md` $\rightarrow$ `AuthorizationModule/`
*   `Impersonation.md` $\rightarrow$ `ImpersonationModule/`
*   `UserManagement.md` $\rightarrow$ `UserManagementModule/`

#### C. Operations Context (`app/Tenant/OperationsContext/`)
*   `ActivityLog.md` $\rightarrow$ `ActivityLogModule/`
*   `Notification.md` $\rightarrow$ `NotificationModule/`
*   `Queue.md` $\rightarrow$ `QueueModule/`
*   `Reporting.md` $\rightarrow$ `ReportingModule/`
*   `Webhook.md` $\rightarrow$ `WebhookModule/`

#### D. Platform Context (`app/Tenant/PlatformContext/`)
*   `ErrorHandling.md` $\rightarrow$ `ErrorHandlingModule/`
*   `ExportImport.md` $\rightarrow$ `ExportImportModule/`
*   `FileUpload.md` $\rightarrow$ `FileUploadModule/`
*   `Workspace.md` $\rightarrow$ `WorkspaceModule/`

---

## 4. Análisis de Calidad y Estilo de Código

El código implementa con excelencia los requerimientos no funcionales de mantenibilidad y robustez de Laravel:

1.  **Tipado Estricto Extremo:** Todos los archivos analizados inician con `declare(strict_types=1);`. Los métodos declaran retornos explícitos y todos los parámetros usan type-hinting.
2.  **Modularidad Limpia (Actions & DTOs):**
    *   La lógica de negocio está encapsulada en Actions (ej. `CreatePlanAction`, `CreateTenantAction`). Son clases `final` con un único método público `execute()`, lo que facilita las pruebas unitarias y su reutilización.
    *   La transferencia de datos se realiza a través de DTOs inmutables (ej. `CreateTenantData`, `CreatePlanData`), eliminando los arrays asociativos crudos propensos a errores.
3.  **Seguridad y Políticas:** Cada módulo expone su Policy correspondiente (ej. `PlanPolicy`, `TenantSubscriptionPolicy`) para el control de acceso en controladores y Livewire.
4.  **Uso de Livewire y Formularios:** Se utilizan formularios encapsulados con `Livewire\Form` (ej. `PlanForm`, `SubscriptionForm`), limpiando la lógica de los componentes de UI y garantizando validaciones consistentes.

---

## 5. Brechas Técnicas Identificadas (Gaps)

Aunque el ecosistema está sumamente completo, existen brechas frente al diseño final documentado en `plinth/`:

1.  **Ausencia del Trait `BelongsToTenant`:** Ninguno de los modelos operacionales en `app/Tenant/` hace uso de un trait de tenencia para aislamiento lógico debido al uso del aislamiento físico (Multi-DB).
2.  **Inconsistencia en la Definición de Single-DB:** En `config/tenancy.php` se encuentra habilitado `DatabaseTenancyBootstrapper`, y en las configuraciones de migración de tenant se define la ruta `migrations/tenant`, forzando la creación y migración de múltiples bases de datos físicas en Postgres en lugar del esquema lógico centralizado de Single-DB.
3.  **Dependencias de Facturación:** El PRD menciona la integración comercial propietaria mediante `nandocdev/plinth-multitenant-billing` y soporte dLocal para cobros recurrentes. El módulo de Billing mantiene lógicas personalizadas de facturación dentro de `app/Central/BillingModule`, las cuales deben integrarse fluidamente con el motor de tenencia.

---

## 6. Hoja de Ruta Sugerida para Alinear la Arquitectura

Si la meta definitiva es pasar al esquema **Single-DB (Aislamiento Lógico)** especificado en la documentación de `plinth/PRD.md`, se debe seguir este plan de migración:

### Fase 1: Desactivación del Switching de Conexiones
1.  En `config/tenancy.php`, retirar `Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class` de la lista de `bootstrappers`.
2.  Configurar la conexión por defecto de la aplicación central como la conexión activa permanente para consultas operacionales de tenant.

### Fase 2: Unificación del Esquema de Migraciones
1.  Mover todas las migraciones ubicadas en `database/migrations/tenant` hacia la carpeta raíz o a una carpeta común de ejecución global.
2.  Modificar cada una de las tablas operacionales del tenant (como `users`, `invitations`, `notifications`, `webhook_endpoints`) agregando:
    ```php
    $table->string('tenant_id');
    $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
    $table->index(['tenant_id', 'created_at']); // O índices compuestos adecuados
    ```
3.  Eliminar el parámetro de ruta en `config/tenancy.php` para `migrations/tenant`.

### Fase 3: Integración del Trait de Aislamiento Lógico
1.  Importar e implementar el trait `Stancl\Tenancy\Database\Concerns\BelongsToTenant` en todos los modelos que residen en el contexto Tenant (`app/Tenant/`):
    ```php
    use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

    final class User extends Authenticatable {
        use BelongsToTenant;
        // ...
    }
    ```
2.  Asegurar que todas las llamadas de base de datos directas o personalizadas usen Eloquent para garantizar que el Global Scope filtre automáticamente las consultas por el `tenant_id` del tenant inicializado actualmente.
