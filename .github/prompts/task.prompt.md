---
name: task-saas
description: Ejecutar una tarea del ROADMAP en SaaS-Kit-2026 (Monolito Modular con Multi-Tenancy) usando Laravel 12 + Livewire 4 + PostgreSQL, respetando estrictamente la arquitectura de Bounded Contexts.
---

## 🎯 Contexto obligatorio

Proyecto: SaaS-Kit-2026  
Arquitectura: Monolito Modular por Bounded Contexts (Central / Tenant / Shared)  
Stack: Laravel 12 + PHP 8.3+ + Livewire 4 + PostgreSQL 16 + Redis + Horizon + Pulse

Documentación base (leer SIEMPRE antes de generar código):

- docs/project/00_Plan.md → Arquitectura modular, estructura de directorios, flujo de provisioning
- docs/project/01_Dependencias.md → Paquetes requeridos y justificaciones
- docs/project/02_Caracteristicas.md → Features priorizadas del kit
- docs/project/03_VISION.md → Alcance, actores, KPIs
- docs/project/04_ARCHITECTURE.md → SAD completo, decisiones arquitectónicas, flujos
- docs/project/05_TENANCY.md → Estrategia multi-tenancy, scoping, isolation tests
- docs/project/06_PROVISIONING.md → Flujo de onboarding, ProvisionTenantJob, idempotencia
- docs/project/07_BILLING.md → Cashier, Stripe, webhooks, feature flags
- docs/project/08_PERMISSIONS.md → RBAC + Teams con spatie/laravel-permission
- docs/project/09_ROADMAP.md → Milestones, DoD, secuencia de construcción

---

## 🚫 Reglas Inquebrantables

- **Bounded Contexts:** Central / Tenant / Shared — sin acoplamientos directos entre contextos
- **Livewire** = orquestador UI → NUNCA lógica de negocio
- **Lógica de negocio** → SOLO en Actions (una responsabilidad por Action)
- **Validación** → Livewire Forms Objects (NO FormRequest en web)
- **Escrituras** → `DB::transaction()` obligatorio
- **Autorización** → Policies + spatie/laravel-permission SIEMPRE
- **Módulos NO se acoplan directamente** → usar Events o DTOs compartidos (app/Shared)
- **Multi-tenancy first:** Todo código tenant-aware desde el inicio (`tenancy()->initialize()`)
- **PostgreSQL nativo** (jsonb, constraints, índices — sin sintaxis MySQL)
- **Evitar N+1** → eager loading obligatorio (`with()`)
- **Jobs asíncronos** para operaciones pesadas (provisioning, emails, webhooks)
- No sobreingeniería

---

## ⚙️ Tarea a ejecutar

{{TAREA_DEL_ROADMAP}}

Ejemplos:

- M2 — Implementar `ProvisionTenantJob` con idempotencia y retry
- M2 — Crear `BillingModule`: modelo `Plan`, `Subscription`, `CreateSubscriptionAction`
- M3 — Implementar flujo de invitaciones a Team con email + accept

---

## 🧠 Proceso interno

### Paso 1 — Análisis de la tarea

Identificar:

- Bounded Context destino (Central / Tenant / Shared)
- Módulo destino (`AuthenticationModule`, `TenantProvisioningModule`, `BillingModule`, etc.)
- Entidades involucradas
- Si requiere contexto central, tenant o ambos

Determinar:

- DTO necesario (en `app/Shared/DTOs/` o dentro del módulo)
- Action principal
- Job (si operación pesada o asíncrona)
- Events + Listeners (si hay interacción entre módulos o contextos)
- Policy requerida
- Validaciones críticas (DB constraints + lógica)

Detectar riesgos:

- Race conditions (ShouldBeUnique + transaction)
- Fugas de datos entre tenants (tenant isolation)
- N+1 queries
- Jobs corriendo en contexto de tenant equivocado (TenantAware trait)
- Webhooks no idempotentes
- Migraciones que deben ir en `central/` vs `tenant/`

### Paso 2 — Iniciar rama Git

**Antes de generar cualquier archivo**, ejecutar:

```bash
# Asegurar que develop está actualizado
git checkout develop
git pull origin develop

# Crear rama de feature desde develop
# Convención: feat/<milestone>/<modulo>-<descripcion-kebab>
# Ejemplos:
#   feat/m2/provisioning-tenant-job
#   feat/m2/billing-subscription-action
#   feat/m3/workspace-invitations
git checkout -b feat/<milestone>/<modulo>-<descripcion-kebab>
```

> La rama debe crearse SIEMPRE desde `develop`, nunca desde `main`.

### Paso 3 — Implementación

Generar todos los artefactos requeridos según el output obligatorio definido abajo.

### Paso 4 — Commits atómicos

Al finalizar la implementación, realizar commits atómicos en orden lógico de dependencia:

```bash
# Orden recomendado (adaptar según tarea):
git add database/migrations/...
git commit -m "chore(<modulo>): agregar migración <nombre_tabla>"

git add app/Shared/DTOs/...
git commit -m "feat(<modulo>): agregar DTO <NombreDTO>"

git add app/{Context}/{Modulo}/Models/...
git commit -m "feat(<modulo>): agregar modelo <NombreModelo>"

git add app/{Context}/{Modulo}/Actions/...
git commit -m "feat(<modulo>): implementar <NombreAction>"

git add app/{Context}/{Modulo}/Jobs/...
git commit -m "feat(<modulo>): agregar job <NombreJob>"

git add app/{Context}/{Modulo}/Events/... app/{Context}/{Modulo}/Listeners/...
git commit -m "feat(<modulo>): agregar evento <NombreEvento> y listener"

git add app/{Context}/{Modulo}/Policies/...
git commit -m "feat(<modulo>): agregar policy <NombrePolicy>"

git add app/{Context}/{Modulo}/Livewire/... resources/views/...
git commit -m "feat(<modulo>): agregar componente Livewire <NombreComponente>"

git add app/{Context}/{Modulo}/Providers/...
git commit -m "chore(<modulo>): registrar módulo en ModuleServiceProvider"

git add tests/...
git commit -m "test(<modulo>): agregar tests de <funcionalidad>"
```

Reglas de commit:

- Un commit por unidad lógica (no mezclar migration + action en el mismo commit)
- No mezclar contextos (Central vs Tenant) en un mismo commit
- Mensajes en español, formato Conventional Commits

### Paso 5 — Merge a develop

```bash
# Volver a develop y hacer merge
git checkout develop
git pull origin develop

# Merge con --no-ff para preservar historial de la feature
git merge --no-ff feat/<milestone>/<modulo>-<descripcion-kebab> \
  -m "feat(<modulo>): merge <descripcion-completa> → develop"

# Push
git push origin develop

# Eliminar rama local (opcional, confirmar con equipo)
git branch -d feat/<milestone>/<modulo>-<descripcion-kebab>
```

> Si hay conflictos en el merge, resolverlos antes del push. Nunca usar `--force` en `develop`.

---

## Actualizacion de documentación

Actualizar el estado de la tarea en el archivo `docs/technical/features.md` bajo el apartado correspondiente al módulo y milestone, marcando la tarea como "Implementado" o "Parcial" según corresponda.

## 📦 Output obligatorio (código listo para producción)

Generar SOLO lo necesario, separado por archivos reales:

### Backend (obligatorio según tarea)

- Model (Central o Tenant según corresponda)
- Migration (en `database/migrations/central/` o `database/migrations/tenant/`)
- DTO (`readonly`, tipado estricto PHP 8.3)
- Action (transaccional, responsabilidad única)
- Job (si operación asíncrona — con `ShouldQueue`, `ShouldBeUnique` si aplica)
- Event + Listener (si hay comunicación entre módulos)
- Policy

### Livewire (obligatorio en tareas con UI)

- Componente Livewire (orquestador, sin lógica de negocio)
- Livewire Form Object (validación)
- Blade (Flux UI: `<flux:input>`, `<flux:button>`, etc.)

### Infraestructura

- Routes (`Routes/web.php` del módulo)
- Registro en `ModuleServiceProvider` del módulo correspondiente

---

## ⚠️ Reglas Livewire específicas

- Usar `wire:model` con Form Objects
- Usar `wire:submit`
- Redirecciones con `navigate: true`
- UI SIEMPRE con Flux UI (prohibido HTML plano si existe componente equivalente)
- Componente Livewire solo delega a Action — cero queries directas

---

## 🧪 Validaciones mínimas obligatorias

- No duplicados (DB constraint + validación Form Object)
- Verificar ownership y tenant scope (nunca asumir contexto global)
- Validar estado previo del recurso (ej: no provisionar tenant ya activo)
- Manejo de errores: `failed()` en Jobs, try/catch en Actions, log estructurado

---

## 🔍 Checklist de validación

- [ ] Rama creada desde `develop` con convención `feat/<milestone>/<modulo>-<descripcion>`
- [ ] Código dentro de `app/Central/{Módulo}`, `app/Tenant/{Módulo}` o `app/Shared/`
- [ ] Migración en el path correcto (`central/` vs `tenant/`)
- [ ] Livewire NO contiene lógica de negocio
- [ ] Action usa `DB::transaction()`
- [ ] DTO tipado y usado correctamente
- [ ] Policy aplicada en Livewire (`$this->authorize()`)
- [ ] Sin N+1 (uso de `with()`)
- [ ] Jobs usan `TenantAware` trait si operan en contexto tenant
- [ ] PostgreSQL idiomático (sin sintaxis MySQL)
- [ ] Índices y constraints definidos en migración
- [ ] Events usados para comunicación cross-módulo
- [ ] UI con Flux UI
- [ ] PHP 8.3 tipado estricto (`declare(strict_types=1)`)
- [ ] Sin acoplamiento directo entre Bounded Contexts
- [ ] Commits atómicos realizados en orden lógico
- [ ] Merge `--no-ff` a `develop` completado

---

## ⚠️ Anti-patrones prohibidos

- CRUD directo en Livewire
- Queries dentro de Blade
- Lógica de negocio en Models (fat models)
- Llamar directamente a otro Bounded Context sin Event/DTO
- `tenancy()->initialize()` olvidado en Jobs tenant-aware
- `Artisan::call()` síncrono en request HTTP
- Migraciones tenant en path `central/` o viceversa
- Validaciones sin constraint en DB
- `DB::raw()` innecesario
- Commits mezclando múltiples responsabilidades
- `git merge --ff` o `git push --force` en `develop`

---

## 🧾 Entrega final

- Código completo, listo para copiar/pegar
- Separado por paths reales (`app/Central/BillingModule/Actions/CreateSubscriptionAction.php`)
- Comandos Git ejecutados y confirmados (rama creada → commits → merge)
- Sin explicaciones largas
- Comentarios solo donde hay riesgo no obvio (race condition, tenancy switch, etc.)
