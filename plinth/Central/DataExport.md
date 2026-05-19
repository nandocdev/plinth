# Central Module: Data Export (Exportación de Datos Globales)

## 1. Propósito
El módulo **Data Export** permite a los administradores de la plataforma Plinth generar y descargar reportes masivos de la base de datos global. Está diseñado para manejar grandes volúmenes de información (tenants, suscripciones, facturación, auditoría) de forma asíncrona, garantizando que el rendimiento de la aplicación no se vea afectado durante el procesamiento.

## 2. Arquitectura de Datos

### 2.1 Modelos Principales
- **`CentralDataExport`**: Representa una solicitud de exportación. Almacena el estado del proceso (pending, processing, completed, failed), los filtros aplicados, la ruta del archivo generado y el administrador que solicitó la data.

## 3. Capa de Lógica (Acciones)

### 3.1 Orquestación de Exportaciones
- **`QueueCentralDataExportAction`**: Recibe la solicitud del usuario, valida los permisos y pone en cola el Job de generación de datos.
- **`BuildCentralDataExportPayloadAction`**: Contiene la lógica de consulta (querying) para extraer la data según los filtros (ej. rango de fechas, estado de tenants, planes específicos).
- **`ListCentralDataExportsAction`**: Proporciona el historial de archivos generados para su posterior descarga o reintento.
- **`ListCentralExportTenantOptionsAction`**: Facilita la selección de tenants específicos para reportes segmentados.

## 4. Tareas Asíncronas (Jobs)
- **`GenerateCentralDataExportJob`**: El "motor" del módulo. Ejecuta la consulta pesada, transforma los datos a formatos como CSV o Excel (usando buffers para ahorrar memoria) y notifica al usuario cuando el archivo está listo.

## 5. Capa de Presentación (UI)

### 5.1 Componentes Livewire
- **`CentralDataExportManager`**: Interfaz unificada para solicitar nuevas exportaciones, monitorear el progreso en tiempo real y acceder al historial de descargas.

### 5.2 Controladores
- **`DownloadCentralDataExportController`**: Gestiona la entrega segura del archivo generado, asegurando que solo el autor de la solicitud o un SuperAdmin pueda acceder al link de descarga temporal.

## 6. Seguridad y Autorización

### 6.1 Policies
- **`CentralDataExportPolicy`**: Restringe el acceso a la generación de reportes a usuarios con permisos de auditoría o administración total (`central.exports.manage`).

### 6.2 Almacenamiento Seguro
- Los archivos generados se guardan en el disco `private` para evitar accesos directos vía URL. La descarga se realiza mediante un Stream autenticado que valida la sesión del administrador.

## 7. Consideraciones Técnicas
- El sistema utiliza **Chunking** de Eloquent durante la exportación para evitar problemas de agotamiento de memoria RAM con tablas de millones de registros.
- Se implementan eventos de sistema (`CentralDataExportCompleted`, `CentralDataExportFailed`) para registrar el éxito o fracaso en el rastro de auditoría global.
