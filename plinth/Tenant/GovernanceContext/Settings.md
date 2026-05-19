# Tenant Module: Settings (Configuración y Marca del Cliente)

## 1. Propósito
El módulo **Settings** es el centro de personalización del tenant. Permite a los administradores de la organización configurar su identidad visual (Branding), ajustar preferencias regionales y definir parámetros operativos globales que afectan a todos los usuarios del espacio de trabajo.

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Preferencias
- **`GetTenantSettingsAction`**: Recupera el conjunto completo de configuraciones del tenant, combinando valores por defecto con las personalizaciones del cliente.
- **`UpdateTenantSettingsAction`**: Valida y persiste los cambios en la configuración. Gestiona la actualización de:
    - **Branding:** Logos (Light/Dark mode), Isotipo, colores corporativos primarios y secundarios.
    - **Localización:** Zona horaria predeterminada, formato de fecha, moneda base y lenguaje de la interfaz.
    - **Seguridad:** Políticas de contraseñas propias, requerimiento de 2FA para sus usuarios.

## 3. Capa de Presentación (UI)

### 3.1 Panel de Configuración
- **`TenantGeneralSettings`**: Interfaz unificada con pestañas para organizar las diferentes categorías de configuración.
- **`BrandingPreviewer`**: Herramienta visual que permite al cliente ver cómo se aplicarán sus colores y logos a la interfaz de Plinth en tiempo real.

## 4. Personalización Visual (CSS Dinámico)
El módulo genera variables de CSS personalizadas (`--primary-color`, etc.) basadas en la configuración del tenant, las cuales se inyectan en el layout global para lograr una experiencia White-label completa.

## 5. Seguridad y Aislamiento
- **Aislamiento de Datos:** Las configuraciones se almacenan en la columna JSON de la tabla `tenants` o en una tabla de settings aislada por `tenant_id` (según la escala).
- **Policies:** Solo el rol de Administrador puede realizar cambios en la configuración global de la organización.

## 6. Consideraciones Técnicas
- El sistema utiliza **Caching de Configuración** para evitar que cada carga de página requiera consultar la base de datos para obtener los colores o el logo del cliente.
- Las imágenes de marca (logos) se gestionan mediante el `MediaLibrary`, asegurando que el almacenamiento esté organizado por tenant.
