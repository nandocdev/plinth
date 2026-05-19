# Tenant Module: Export & Import (Transferencia de Datos)

## 1. Propósito
El módulo **Export & Import** facilita la soberanía de datos para los clientes de Plinth. Permite a los usuarios del tenant extraer su información en formatos estándar (CSV, Excel) e importar datos de forma masiva para poblar sus catálogos o registros iniciales.

## 2. Capa de Lógica (Acciones)

### 2.1 Transferencia de Datos
- **`QueueTenantCsvExportAction`**: Inicia el proceso asíncrono de generación de un archivo CSV con la data del tenant. Utiliza el Global Scope de `tenant_id` para garantizar que solo se exporten sus registros.
- **`QueueTenantCsvImportAction`**: Recibe un archivo subido por el cliente, lo valida y pone en cola el proceso de inserción masiva.
- **`ListTenantCsvTransferRunsAction`**: Proporciona el historial de importaciones y exportaciones realizadas por la organización, indicando el éxito, errores de validación encontrados y el tiempo de procesamiento.

## 3. Tareas Asíncronas (Jobs)
- **`ProcessTenantCsvImportJob`**: Lee el archivo por partes (chunking), valida cada fila contra las reglas de negocio del tenant e inserta los registros en la base de datos única, asignando automáticamente el `tenant_id` correspondiente.

## 4. Capa de Presentación (UI)

### 4.1 Gestor de Transferencias
- **`DataTransferCenter`**: Interfaz donde el cliente carga sus archivos de importación, descarga sus exportaciones generadas y visualiza bitácoras de errores de carga (ej. "Fila 45: El email ya existe").

## 5. Seguridad y Aislamiento
- **Aislamiento de Archivos:** Los archivos temporales de exportación se guardan en el disco privado del tenant (`storage/app/tenants/{tenant_id}/exports`).
- **Validación Estricta:** Durante la importación, el sistema rechaza cualquier registro que intente forzar un `tenant_id` diferente al contexto activo.

## 6. Consideraciones Técnicas
- El uso de **Jobs** y **Batches** de Laravel permite manejar archivos de miles de filas sin agotar la memoria del servidor compartido.
- Se implementan **Validaciones Dinámicas** para asegurar que los datos importados cumplan con las restricciones de integridad antes de persistirlos.
