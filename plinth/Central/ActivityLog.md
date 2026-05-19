# Central Module: Activity Log (Auditoría Centralizada)

## 1. Propósito
El módulo **Activity Log** en el contexto Central es responsable de capturar, persistir y visualizar todas las acciones críticas realizadas por los administradores globales en el panel de control. Además, sirve como un hub para monitorear eventos relacionados con los tenants desde una perspectiva centralizada.

## 2. Arquitectura del Módulo

### 2.1 Modelo: `ActivityLogEntry`
- **Base:** Hereda de `Spatie\Activitylog\Models\Activity`.
- **Almacenamiento:** Utiliza la tabla global `activity_log`.
- **Aislamiento Single-DB:** En el contexto Central, los registros no están filtrados por un `tenant_id` en la raíz de la tabla, sino que pueden contener una referencia al tenant dentro de la columna JSON `properties`.

## 3. Capa de Lógica (Acciones)

### 3.1 Recuperación y Filtrado
- **`ListGlobalLogsAction`**: Orquesta la recuperación de logs con capacidades de filtrado avanzado:
    - Búsqueda por texto (descripción, evento, subject).
    - Filtro por `tenant_id` (extraído del JSON `properties`).
    - Filtro por tipo de evento.
    - Filtro por administrador (causer).
- **`ListAdminLogFilterOptionsAction`**: Recupera la lista de administradores únicos que han generado logs para poblar los filtros de la UI.
- **`ListTenantLogFilterOptionsAction`**: Recupera la lista de tenants referenciados en los logs para facilitar el filtrado por cliente.

## 4. Middleware de Auditoría
- **`RecordCentralAuditTrail`**: Este middleware es el motor de captura automática.
    - **Acciones Capturadas:** Solicitudes HTTP (POST, PUT, PATCH, DELETE) y acciones de Livewire.
    - **Sanitización:** Elimina automáticamente datos sensibles como `password`, `token`, y secretos de 2FA.
    - **Detección de Contexto:** Intenta extraer el `tenant_id` de la ruta o del input para vincular la acción administrativa a un cliente específico.

## 5. Capa de Presentación (UI)

### 5.1 Componentes Livewire
- **`GlobalLogsViewer`**: Interfaz reactiva para la exploración de logs.
    - Tabla con paginación.
    - Formulario de filtros dinámicos (`GlobalLogsFilterForm`).
    - Visualización detallada de cambios (diffs) y propiedades del request original.

## 6. Seguridad y Autorización

### 6.1 Policies
- **`ActivityLogEntryPolicy`**: Restringe el acceso a los logs solo a usuarios con el permiso `central.logs.view`.
- **Inmutabilidad:** Siguiendo las directrices de `GEMINI.md`, los logs son inmutables. No existen acciones de edición o borrado para garantizar la integridad de la auditoría.

## 7. Consideraciones de Single-DB
- Para mantener la eficiencia en el esquema de base de datos única, las búsquedas por `tenant_id` se realizan sobre la columna JSON. En instalaciones de gran escala, Plinth está preparado para utilizar columnas virtuales generadas o índices funcionales sobre `properties->'id'`.
