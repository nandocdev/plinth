---
name: task
description: Ejecutar una tarea del ROADMAP en HoarariosWFM (Monolito Modular) usando Laravel + Livewire + PostgreSQL, respetando estrictamente la arquitectura.
---

## 🎯 Contexto obligatorio

Proyecto: HoarariosWFM
Arquitectura: Monolito Modular (app/Modules)
Stack: Laravel 12 + Livewire 3 + FluxUI + PostgreSQL

Documentación base (leer SIEMPRE antes de generar código):

- docs/technical/07_Arquitectura.md
- docs/technical/08_ModuleModels.md
- docs/technical/05_DDL.md
- docs/technical/04_model.md
- docs/technical/03_casos_uso.md
- docs/technical/02_requisitos.md
- docs/technical/06_Permisos.md
- docs/technical/ROADMAP.md

---

## 🚫 Reglas Inquebrantables

- Livewire = controlador UI → NUNCA lógica de negocio
- Lógica de negocio → SOLO en Actions (una responsabilidad)
- Validación → Livewire Forms (NO FormRequest en web)
- Escrituras → DB::transaction()
- Autorización → Policies SIEMPRE
- Módulos NO se acoplan directamente (usar Events o DTOs)
- PostgreSQL nativo (jsonb, constraints, índices)
- Evitar N+1 (usar eager loading obligatorio)
- No sobreingeniería

---

## ⚙️ Tarea a ejecutar

{{TAREA_DEL_ROADMAP}}

Ejemplo:

- UC-OP-05 — Solicitud de Permiso Total con validación de duplicados

---

## 🧠 Proceso interno (Copilot)

1. Identificar:
    - Módulo destino
    - Entidades involucradas
    - Caso de uso asociado

2. Determinar:
    - DTO necesario
    - Action principal
    - Eventos (si aplica)
    - Policy requerida
    - Validaciones críticas

3. Detectar riesgos:
    - race conditions
    - duplicados (constraints)
    - N+1 queries
    - dependencias entre módulos

---

## 📦 Output obligatorio (código listo para producción)

Generar SOLO lo necesario, separado por archivos reales:

### Backend (obligatorio)

- Model (si aplica)
- Migration (PostgreSQL optimizada)
- DTO (readonly, tipado estricto)
- Action (transaccional)
- Event + Listener (si aplica)
- Policy

### Livewire (obligatorio en UI)

- Componente Livewire (orquestador)
- Livewire Form (validación)
- Blade con FluxUI (`<flux:input>`, `<flux:button>`, etc.)

### Infraestructura

- Routes (web.php del módulo)
- Registro en ModuleServiceProvider

---

## ⚠️ Reglas Livewire específicas

- Usar `wire:model` con Form Objects
- Usar `wire:submit`
- Redirecciones con `navigate: true`
- UI SIEMPRE con FluxUI (prohibido HTML plano si existe componente)
- NO lógica en métodos del componente (solo delegación)

---

## 🧪 Validaciones mínimas obligatorias

- No permitir duplicados (DB constraint + validación)
- Validar ownership (`.own`, `.team`)
- Validar estado (ej: no crear sobre registros publicados)
- Manejo de errores consistente

---

## 🔍 Checklist de validación (Copilot debe cumplir TODO)

- [ ] Código dentro de app/Modules/{Modulo}
- [ ] Livewire NO contiene lógica de negocio
- [ ] Action implementa DB::transaction()
- [ ] DTO usado correctamente
- [ ] Policy aplicada en Livewire
- [ ] Sin N+1 queries (uso de with())
- [ ] Uso correcto de PostgreSQL (sin sintaxis MySQL)
- [ ] Índices y constraints definidos
- [ ] Eventos usados si hay interacción entre módulos
- [ ] UI construida con FluxUI
- [ ] Código tipado (PHP 8.2+)
- [ ] Sin dependencias cruzadas entre módulos

---

## ⚠️ Anti-patrones prohibidos

- CRUD directo en Livewire
- Queries dentro de Blade
- Lógica en Models (fat models)
- Llamar otro módulo directamente
- Uso de DB::raw innecesario
- Validaciones duplicadas sin constraint en DB

---

## 🧾 Entrega final

- Código completo, listo para copiar/pegar
- Separado por archivos (paths reales)
- Sin explicaciones largas
- Comentarios solo si son críticos

---

## 🧩 Commits (OBLIGATORIO)

Generar commits atómicos usando Conventional Commits en español:

Formato:
<tipo>(<modulo>): <descripcion>

Tipos:

- feat
- fix
- refactor
- test
- docs
- chore

Ejemplo:

feat(workflows): implementar solicitud de permiso total
feat(workflows): agregar validación de duplicados en leave_requests
fix(workflows): corregir race condition en aprobación de permisos
refactor(workflows): extraer lógica a LeaveRequestAction

Reglas:

- Un commit por unidad lógica
- No mezclar responsabilidades
- Mensajes claros y técnicos
