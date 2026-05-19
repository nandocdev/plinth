# Central Module: Tenant Provisioning (Aprovisionamiento de Clientes)

## 1. Propósito
El módulo **Tenant Provisioning** es el núcleo operativo de Plinth. Se encarga del ciclo de vida completo de los tenants: desde el registro inicial (público o administrativo) y la configuración de dominios, hasta la gestión de respaldos, la suspensión de cuentas y la impersonación para soporte técnico.

## 2. Arquitectura de Datos

### 2.1 Modelos Principales
- **`Tenant`**: Extiende el modelo base de `stancl/tenancy`. Almacena la configuración crítica, el estado del cliente y metadatos de personalización.
- **`Domain`**: Gestiona los subdominios (ej. `cliente.plinth.com`) y dominios personalizados (ej. `app.cliente.com`).
- **`TenantImpersonationToken`**: Almacena tokens temporales y seguros para permitir que administradores de Central entren al panel de un tenant sin conocer sus credenciales.
- **`TenantRecoverySnapshot`**: Registro de copias de seguridad de la base de datos y archivos específicas de un tenant.
- **`TenantProvisioningHookRun`**: Auditoría de la ejecución de hooks automáticos tras la creación de un cliente.

## 3. Capa de Lógica (Acciones)

### 3.1 Gestión de Ciclo de Vida
- **`CreateTenantAction`**: Orquesta la creación técnica del tenant, incluyendo la base de datos (en Single-DB, la inicialización de registros base) y la asignación de planes iniciales.
- **`RegisterPublicTenantAction`**: Especialización para el auto-registro de clientes desde la página web pública.
- **`CompleteTenantOnboardingAction`**: Finaliza la configuración post-creación (ej. configuración de marca, primer usuario admin del tenant).
- **`SuspendTenantAction`**: Bloquea el acceso al tenant por falta de pago o violaciones de términos.
- **`DeleteTenantAction`**: Eliminación (soft o definitiva) del cliente y sus recursos asociados.

### 3.2 Gestión de Dominios
- **`CreateDomainAction`** / **`DeleteDomainAction`**: Gestión de subdominios y dominios externos.
- **`VerifyDomainAction`**: Verifica que los registros DNS de un dominio personalizado apunten correctamente a Plinth.

### 3.3 Soporte y Recuperación
- **`StartTenantImpersonationAction`**: Genera una URL segura para que un admin central "entre" como un usuario del tenant.
- **`ConsumeTenantImpersonationAction`**: Valida y destruye el token de impersonación al completar el acceso.
- **`QueueTenantBackupAction`** / **`QueueTenantRestoreAction`**: Gestiona las copias de seguridad específicas del contexto del cliente.

## 4. Tareas Asíncronas (Jobs)
- **`RunTenantProvisioningHooksJob`**: Ejecuta tareas post-instalación (ej. enviar email de bienvenida, aprovisionar recursos en la nube).
- **`RunTenantBackupJob`**: Proceso pesado de exportación de datos del tenant bajo el esquema Single-DB.

## 5. Capa de Presentación (UI)

### 5.1 Componentes Livewire
- **`PublicTenantSignup`**: Formulario público de registro de nuevos clientes.
- **`TenantCrud`**: Panel maestro administrativo para gestionar la lista de clientes.
- **`TenantOnboardingWizard`**: Flujo paso a paso para configurar nuevos clientes de forma guiada.

## 6. Seguridad y Autorización

### 6.1 Policies
- **`TenantPolicy`**: Controla quién puede ver, crear o suspender tenants (`central.tenants.manage`).
- **`DomainPolicy`**: Gestiona la edición de dominios y subdominios.

### 6.2 Impersonación Segura
La impersonación utiliza un sistema de "One-Time Token" (OTT) que expira en segundos y está vinculado a la IP del administrador, cumpliendo con los más altos estándares de seguridad.

## 7. Consideraciones de Single-DB
- El aprovisionamiento en Plinth no implica crear bases de datos físicas nuevas, sino inicializar el `tenant_id` en las tablas globales y asegurar que los Global Scopes estén listos para el nuevo cliente.
- Las consultas de administración global utilizan `withoutTenancy()` para poder listar y gestionar todos los clientes desde un único panel.
