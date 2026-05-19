# Shared Context: Infrastructure (Componentes Base de Sistema)

## 1. Propósito
El módulo **Infrastructure** dentro de Shared agrupa las clases fundamentales que sostienen la arquitectura técnica de Plinth. Aquí residen los Service Providers base, middlewares transversales y clases de soporte para tareas de bajo nivel.

## 2. Componentes Clave

### 2.1 Http (Middleware)
- Contiene los middlewares de auditoría compartidos y los filtros de seguridad que se aplican a nivel de toda la plataforma antes de que la petición llegue a los contextos específicos de Central o Tenant.

### 2.2 Jobs (Base)
- Define las clases base y traits necesarios para que cualquier Job en Plinth sea compatible con el sistema de colas multi-tenant (Single-DB), asegurando la persistencia del `tenant_id` durante la ejecución asíncrona.

### 2.3 Providers
- **`PlinthCoreServiceProvider`**: Registra los bindings globales y las configuraciones core que no pertenecen exclusivamente a un solo módulo.

## 3. Consideraciones Técnicas
- Los componentes de este módulo deben ser agnósticos a la lógica de negocio; su función es puramente estructural y de soporte a la infraestructura de Laravel y Stancl Tenancy.
