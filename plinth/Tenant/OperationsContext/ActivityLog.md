# Tenant Module: Activity Log (Auditoría del Cliente)

## 1. Propósito
El módulo **Activity Log** en el contexto del Tenant es responsable de capturar y mostrar todas las acciones críticas realizadas por los usuarios dentro del panel de su organización. Proporciona una bitácora de auditoría inmutable que permite a los administradores del tenant supervisar quién hizo qué y cuándo dentro de su entorno aislado.

## 2. Arquitectura de Auditoría

### 2.1 Aislamiento de Logs
- **Single-DB Compliance:** A diferencia del módulo central, todos los logs generados en este contexto incluyen el `tenant_id`. 
- Utiliza la tabla compartida `activity_log`, pero el Global Scope asegura que un cliente solo pueda visualizar sus propios registros.

## 3. Capa de Lógica (Acciones)

### 3.1 Recuperación de Auditoría
- **`ListTenantActivityLogsAction`**: Recupera los registros de actividad del cliente actual con soporte para filtros por:
    - Usuario (causer).
    - Tipo de evento (creación, edición, borrado).
    - Rango de fechas.
    - Entidad afectada (subject).

## 4. Middleware: `RecordTenantAuditTrail`
Este middleware es el encargado de la captura automática de eventos:
- Registra mutaciones HTTP (POST, PUT, DELETE).
- Captura interacciones con componentes Livewire.
- **Sanitización:** Garantiza que los datos sensibles del cliente (contraseñas, API keys propias) nunca se guarden en el log.

## 5. Capa de Presentación (UI)

### 5.1 Componentes Livewire
- **`TenantLogsViewer`**: Una interfaz de usuario reactiva donde los administradores pueden explorar su rastro de auditoría, ver los cambios realizados en formato "diff" y exportar los logs si su plan lo permite.

## 6. Seguridad y Autorización

### 6.1 Policies
- **`ActivityLogPolicy`**: Restringe la visualización de los logs de auditoría solo a usuarios con el permiso `audit.view` dentro de la organización.

## 7. Consideraciones Técnicas
- Los registros son inmutables: no existe lógica de edición o borrado de logs para el cliente.
- El sistema utiliza índices en `[tenant_id, created_at]` para garantizar que la visualización de los logs sea instantánea incluso con grandes volúmenes de actividad.
