# Tenant Module: Landing Builder (Constructor de Páginas)

## 1. Propósito
El módulo **Landing Builder** permite a los clientes de Plinth diseñar y publicar su propia página de inicio o portales públicos (ej. `mi-empresa.plinth.com/portal`) sin necesidad de conocimientos técnicos. Proporciona un sistema de gestión de contenidos (CMS) simplificado y visual basado en bloques predefinidos.

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Contenido
- **`GetOrCreateTenantLandingAction`**: Recupera la configuración actual de la landing page del cliente o inicializa una basada en una plantilla por defecto.
- **`ApplyTenantLandingTemplateAction`**: Permite al cliente cambiar drásticamente el diseño de su página seleccionando una de las plantillas pre-diseñadas por Plinth.
- **`UpdateTenantLandingContentAction`**: Guarda los cambios globales de la página (textos, imágenes, configuración SEO).
- **`UpdateLandingBlockSettingsAction`**: Permite la edición granular de bloques específicos (hero, testimonios, precios, contacto).

## 3. Capa de Presentación (UI)

### 3.1 Editor Visual
- **`LandingEditor`**: Un componente Livewire avanzado que ofrece una experiencia de edición "en vivo". Los cambios se previsualizan instantáneamente antes de ser publicados al dominio público del tenant.
- **`BlockPalette`**: Catálogo de secciones arrastrables que el cliente puede añadir a su página.

## 4. Publicación y SEO
- El módulo genera automáticamente metadatos SEO dinámicos basados en la configuración del cliente.
- Las imágenes subidas para la landing se procesan y optimizan mediante el sistema de archivos del tenant (`PlatformContext`).

## 5. Seguridad y Aislamiento
- **Aislamiento de Datos:** La configuración de la landing se almacena vinculada al `tenant_id`. Las peticiones públicas a la raíz del dominio del tenant renderizan automáticamente esta configuración utilizando los Global Scopes correspondientes.

## 6. Consideraciones Técnicas
- El sistema utiliza **Blade Components** dinámicos para renderizar los bloques de la landing, lo que garantiza una velocidad de carga óptima comparado con constructores basados exclusivamente en JavaScript.
- Se implementa un sistema de versionado simple (draft/published) para permitir que el cliente trabaje en cambios sin afectar la página pública hasta que decida publicarlos.
