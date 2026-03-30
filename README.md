<div align="center">

# SaaS-Kit-2026

Boilerplate SaaS multi-tenant para Laravel con aislamiento fuerte por tenant y arquitectura modular por contextos.

[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Stancl Tenancy](https://img.shields.io/badge/Stancl%20Tenancy-v3-0F172A?style=flat-square)](https://tenancyforlaravel.com)
[![Pest](https://img.shields.io/badge/Tests-Pest-10B981?style=flat-square)](https://pestphp.com)

</div>

## Resumen
Este repositorio implementa un monolito modular con separación estricta entre:

- Central (landlord): administración SaaS global.
- Tenant: aplicación aislada por cliente.
- Shared: infraestructura y contratos compartidos.

El objetivo es evitar fugas de datos por diseño: base de datos, cache, storage y jobs aislados por tenant desde el inicio.

## Estado actual
### Central
- Implementado: autenticación admin, CRUD de tenants/domains, planes y suscripciones.
- Implementado: onboarding, lifecycle de suscripción, límites de uso, health dashboard.
- Implementado: impersonation, backup/restore por tenant, data export, webhooks, auditoría.

### Tenant (MVP)
- Implementado: auth tenant (guard separado).
- Implementado: dashboard y perfil (update de datos + contraseña).
- Implementado: rutas tenant modulares por provider.
- Implementado: modelos tenant sin connection hardcodeada.
- Implementado: storage tenant-aware en storage/app/tenants/{uuid}/.
- Implementado: cache tenant-aware con CacheTenancyBootstrapper.
- Implementado: jobs tenant-aware con restore de contexto y guardrail de middleware.

Estado fuente: [docs/technical/features.md](docs/technical/features.md).

## Stack real del proyecto
- PHP 8.3+
- Laravel 13
- Livewire 4 + Flux
- Stancl Tenancy 3.x
- PostgreSQL 16 (target)
- Redis (target para cache/colas en producción)
- Horizon, Pulse, Pennant
- Pest + Larastan + Pint

Dependencias: [composer.json](composer.json).

## Arquitectura de carpetas
```text
app/
  Central/
    <Modulo>/
  Tenant/
    <Modulo>/
  Shared/
    DTOs/
    Contracts/
    Infrastructure/
    Support/
```

Módulos tenant actuales:
- AuthenticationModule
- WorkspaceModule
- SelfServiceBillingModule
- FeatureFlagsModule
- ImpersonationModule

Módulos central actuales:
- AuthenticationModule
- TenantProvisioningModule
- BillingModule
- ActivityLogModule
- AdminAuthorizationModule
- SystemHealthModule
- NotificationModule
- PartnerWebhookModule
- DataExportModule
- AffiliateModule

## Puesta en marcha rápida
### Requisitos
- PHP 8.3+
- Composer 2+
- Node 18+
- PostgreSQL

### Instalación
```bash
composer setup
```

El script realiza:
- instalación de dependencias PHP y JS,
- creación de .env si no existe,
- key generate,
- migrate,
- build frontend.

### Desarrollo local
```bash
composer dev
```

Levanta en paralelo:
- servidor Laravel,
- listener de queue,
- logs con pail,
- Vite en modo dev.

### Tests y calidad
```bash
composer test
composer lint
composer lint:check
```

## Multi-tenancy en práctica
### Ruteo
- Central: rutas fuera de tenancy middleware.
- Tenant: resolución por dominio con InitializeTenancyByDomain + PreventAccessFromCentralDomains.

### Aislamiento aplicado
- Database: conexión tenant dinámica sin hardcode en modelos tenant.
- Storage: root por tenant en storage/app/tenants/{uuid}/.
- Cache: tags por tenant mediante CacheTenancyBootstrapper.
- Queue jobs: payload con tenant_id y restore automático de contexto en worker.

## Documentación del proyecto
- [docs/project/00_Plan.md](docs/project/00_Plan.md)
- [docs/project/01_Dependencias_Requeridas.md](docs/project/01_Dependencias_Requeridas.md)
- [docs/project/02_Caracteristicas_SaaS_Starter_Kit.md](docs/project/02_Caracteristicas_SaaS_Starter_Kit.md)
- [docs/project/03_VISION.md](docs/project/03_VISION.md)
- [docs/project/04_ARCHITECTURE.md](docs/project/04_ARCHITECTURE.md)
- [docs/project/05_TENANCY.md](docs/project/05_TENANCY.md)
- [docs/project/06_PROVISIONING.md](docs/project/06_PROVISIONING.md)
- [docs/project/07_BILLING.md](docs/project/07_BILLING.md)
- [docs/project/08_PERMISSIONS.md](docs/project/08_PERMISSIONS.md)
- [docs/project/09_INVITATIONS.md](docs/project/09_INVITATIONS.md)
- [docs/project/09_ROADMAP.md](docs/project/09_ROADMAP.md)

## Notas importantes
- La cola database está configurada sobre conexión central y usa tenant_id en payload para jobs tenant-aware.
- En producción se recomienda Redis para cache y colas.
- Los tests de aislamiento tenant son parte del criterio de calidad del template.
