
Boilerplate multi-tenant con Stancl/Tenancy: Central = landlord SaaS management (tenants, billing, admins). Tenant = isolated customer app. Separacion estricta o muerte por fuga de datos.

Estado base usado para este documento:
- Implementado: existe y se usa en el template actual.
- Parcial: existe una base funcional, pero faltan piezas clave para produccion.
- Pendiente: no se encontro implementacion real en el template.

### 1. CENTRAL (Landlord) Context

#### MVP (Minimo viable - lo que debe existir dia 1)
- [Implementado] Registro y login de system admins (guard central)
- [Implementado] CRUD tenants (create, list, suspend, delete)
- [Implementado] CRUD domains (custom domains + verification)
- [Implementado] CRUD planes/subscripciones (precio, features, trial)
- [Implementado] Billing basico: dLocal integration + webhook handler (solo central DB)
- [Implementado] Panel admin simple: lista tenants con estado, usage basico y actions
- [Implementado] Migraciones central + seeder inicial de planes
- [Implementado] Rutas en routes/web.php sin tenancy middleware

##MVP## Produccion (Media - lo que necesita para no morir en prod)
- [Implementado] Tenant onboarding wizard (create tenant + default domain + assign plan)
- [Implementado] Subscription lifecycle: trial -> active -> past_due -> canceled -> deleted
- [Implementado] Usage limits & enforcement (soft/hard limits por plan)
- [Implementado] Global logs viewer (con tenant filter)
- [Implementado] System health dashboard (DB connections, queue status, storage usage)
- [Implementado] Impersonation segura de tenants (solo desde central)
- [Implementado] Email notifications central (new tenant, subscription events)
- [Implementado] Rate limiting + brute force protection en login central
- [Implementado] Backup/restore tenants (DB + storage snapshot)
- [Implementado] Two-factor auth para system admins

#### Avanzadas (Actualizaciones futuras - scaling & enterprise)
- [Implementado] Multi-region / multi-DB support (tenant DB provisioning automatico)
- [Implementado] White-labeling para tenants (branding central configurable)
- [Implementado] Affiliate / referral system
- [Parcial] Advanced analytics agregados (MRR, churn, LTV, tenant growth)
- [Implementado] Role & permission system global para system admins (Spatie)
- [Implementado] Audit log completo de acciones central
- [Implementado] Webhooks salientes para partners (nuevo tenant, etc.)
	- [Implementado] Tenant self-service portal limitado (upgrade plan, view invoices)
- [Implementado] Automated tenant provisioning con Terraform/Ansible hooks
- [Implementado] Data export central (GDPR compliance)

### 2. TENANT Context

#### MVP (Minimo viable - lo que el cliente ve dia 1)
- [Implementado] (Tenant context) Registro / login de usuarios tenant (guard tenant separado)
- [Implementado] (Tenant context) Dashboard basico con welcome + tenant info
- [Implementado] (Tenant context) Profile & password update
- [Implementado] (Tenant context) Rutas en routes/tenant.php con InitializeTenancyByDomain + PreventAccessFromCentralDomains, Recordar que cada modulo contiene su propio archivo de rutas inyectado a su provider
- [Implementado] (Tenant context) Modelos tenant sin $connection definido
- [Implementado] (Tenant context) Storage disk tenant configurado (storage/app/tenants/{uuid}/)
- [Implementado] (Tenant context) Cache con prefijo/tag tenant-aware via bootstrapper
- [Implementado] (Tenant context) Jobs tenant-aware (middleware o restore context)

#### Produccion (Media - lo que necesita para ser usable en prod)
- [Implementado] (Tenant context) User management (CRUD users, roles, permissions - base RBAC)
- [Implementado] (Tenant context) Settings tenant (company info, branding, preferences) con cache tenant-aware
- [Implementado] (Tenant context) Activity log tenant (con tenant_id)
- [Implementado] (Tenant context) File uploads seguros usando tenant disk
- [Implementado] (Tenant context) Notifications tenant (mail + database)
- [Implementado] (Tenant context) API basica con Sanctum (tenant-aware)
- [Implementado] (Tenant context) Queue system con tenant context restore
- [Implementado] (Tenant context) Feature flags por plan (ej: max_users, max_storage)
- [Implementado] (Tenant context) Dark mode + basic Tailwind/Flux UI con layout.tenant separado
- [Implementado] (Tenant context) Error pages y maintenance mode tenant-isolated
- [Implementado] (Tenant context) Export/Import basicos (CSV) con jobs tenant-aware

#### Avanzadas (Actualizaciones futuras - diferenciacion & scaling)
- [Pendiente] (Tenant context) Modular features (plugins/addons instalables por tenant)
- [Pendiente] (Tenant context) Advanced reporting & analytics (tenant-specific)
- [Pendiente] (Tenant context) Webhooks entrantes y salientes tenant
- [Pendiente] (Tenant context) Custom domain full management + SSL auto (Let's Encrypt)
- [Pendiente] (Tenant context) Multi-language + currency per tenant
- [Pendiente] (Tenant context) Team / workspace dentro del tenant (sub-tenancy light)
- [Pendiente] (Tenant context) Audit log avanzado + export
- [Pendiente] (Tenant context) SSO (SAML/OIDC) support
- [Pendiente] (Tenant context) AI features o integrations marketplace
- [Pendiente] (Tenant context) Tenant-specific middleware chain (ej: enforce plan limits)
- [Pendiente] (Tenant context) Real-time (Laravel Echo + tenant-aware channels)
- [Pendiente] (Tenant context) Automated backups por tenant + retention policies

### Reglas Duras del Boilerplate (no negociables)

- [Pendiente] Nunca compartir users table/session/cache/storage/guards entre central y tenant
- [Pendiente] Central routes -> nunca tenancy middleware ni tenant() helper
- [Pendiente] Tenant models -> nunca $connection = 'mysql'
- [Pendiente] Todo job que toque DB -> obligatorio tenant context
- [Pendiente] Billing siempre en central DB. Tenant solo lee estado via relacion o API interna segura.

### Critica directa a decisiones comunes

- "Hagamos todo en un solo contexto para simplificar" -> No. Escala mal y rompe aislamiento.
- "Usemos un solo guard y filtremos por tenant_id" -> Alto riesgo de fuga de datos y bugs de auth.
- "Cache global con prefijo manual cuando me acuerde" -> Fuente de bugs intermitentes entre tenants.
- "Storage en carpeta compartida con subcarpetas" -> Riesgo de leak de archivos y operacion compleja.

### Funcionalidades implementadas no explicitadas

- [Pendiente] Landing Builder visual del tenant con templates, preview y publicacion.
- [Implementado] Wizard de outboarding en /signup publico (multi-step en Flux con stepper custom).
- [Parcial] Flujo de 2FA tenant opcional/sugerible: setup y desactivación desde perfil; challenge/enforcement pendiente.
- [Pendiente] Gestion de facturacion desde tenant: plan actual, cambio de plan, checkout y customer portal.
- [Pendiente] Gestion UI de API Keys por tenant (ademas de la API basica).
- [Pendiente] Gestion UI de webhooks tenant con historial de entregas y retry.
- [Pendiente] Impersonacion bidireccional: inicio desde central y salida segura en tenant.
- [Pendiente] Enforcement de suscripcion activa y limites de plan en rutas tenant.
- [Pendiente] Dashboard central con metricas agregadas (incluye revenue mensual).

### Proximos pasos recomendados (orden realista)

1. Cerrar pendientes de seguridad central: 2FA admins, hardening de login y politicas de rate limit.
2. Completar dominios en central: CRUD real + verificacion + flujo SSL (si aplica).
3. Subir observabilidad: health dashboard y logs globales consolidados.
4. Cerrar huecos tenant: notifications, maintenance/error isolation y export/import con jobs tenant-aware.
5. Endurecer reglas duras con tests de aislamiento (auth, cache, queue, storage) en CI.
