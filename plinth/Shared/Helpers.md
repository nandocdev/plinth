# Shared Context: Helpers (Utilidades Transversales)

## 1. Propósito
El contexto **Shared** contiene utilidades, clases base y helpers que son consumidos tanto por el contexto Central como por el contexto Tenant. Su objetivo es evitar la duplicación de código y estandarizar comportamientos comunes en toda la plataforma.

## 2. Helpers de Interfaz
- **`TenantSidebarMenuHelper`**: Encapsula la lógica para construir dinámicamente el menú lateral del tenant. Filtra las opciones de navegación basándose en:
    - Los permisos del usuario actual.
    - Las Feature Flags activas para el tenant (basadas en su plan).
    - Los complementos (Addons) instalados.

## 3. Utilización
Estos helpers están diseñados como clases estáticas o servicios inyectables que pueden ser llamados desde cualquier componente Livewire o Controller, garantizando una experiencia de usuario consistente independientemente del contexto de tenencia.
