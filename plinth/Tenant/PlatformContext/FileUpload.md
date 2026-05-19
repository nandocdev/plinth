# Tenant Module: File Upload (Gestión de Archivos y Medios)

## 1. Propósito
El módulo **FileUpload** proporciona una infraestructura centralizada para que los clientes de Plinth suban, almacenen y gestionen documentos e imágenes. Utiliza la integración de `spatie/laravel-medialibrary` adaptada para garantizar que todos los archivos estén lógicamente aislados por `tenant_id` y almacenados de forma segura.

## 2. Capa de Lógica (Acciones)

### 2.1 Operaciones de Archivo
- **`StoreTenantUploadedFileAction`**: Valida y persiste un nuevo archivo en el almacenamiento. Gestiona la generación de miniaturas (thumbnails) para imágenes y la asignación del archivo a una entidad específica del tenant (ej. el logo a la configuración, o un adjunto a un usuario).
- **`ListTenantUploadedFilesAction`**: Recupera el catálogo de archivos del cliente, permitiendo filtrar por tipo de archivo, fecha de subida o entidad asociada.
- **`DeleteTenantUploadedFileAction`**: Elimina físicamente el archivo del almacenamiento y limpia sus metadatos en la base de datos única.

## 3. Arquitectura de Almacenamiento
Plinth organiza los archivos siguiendo una estructura estricta:
- **Ruta:** `storage/app/tenants/{tenant_id}/{collection}/{id}`.
- Esta estructura facilita la auditoría de espacio consumido por cliente y simplifica los procesos de borrado masivo de datos si un cliente decide abandonar la plataforma.

## 4. Capa de Presentación (UI)

### 4.1 Componentes Livewire
- **`FileUploader`**: Un componente reactivo que soporta la subida de archivos mediante drag-and-drop, muestra barras de progreso y permite la validación de extensiones y tamaños en el lado del cliente y servidor.
- **`MediaGallery`**: Navegador visual de los archivos subidos por la organización.

## 5. Seguridad y Aislamiento
- **Aislamiento de Acceso:** Las URLs de los archivos pueden configurarse como **Privadas** (requieren una firma temporal generada por Plinth para ser visualizadas) o **Públicas** (para logos o assets de la landing page).
- **Límites de Cuota:** Integrado con el módulo de `FeatureFlags`, este módulo valida que el tenant no exceda su capacidad de almacenamiento contratada antes de permitir una nueva subida.

## 6. Consideraciones Técnicas
- El procesamiento de imágenes (redimensionamiento, optimización) se realiza de forma asíncrona mediante Jobs para no ralentizar la experiencia de usuario.
- Soporta integración con **S3** o almacenamientos compatibles con la API de Amazon para escalabilidad horizontal infinita.
