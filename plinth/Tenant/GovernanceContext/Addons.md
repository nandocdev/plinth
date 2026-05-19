# Tenant Module: Addons (Extensiones y Complementos)

## 1. Propósito
El módulo **Addons** permite a los clientes de Plinth extender las capacidades básicas de su espacio de trabajo mediante la activación de módulos opcionales. Proporciona una capa de flexibilidad que permite al tenant adaptar la plataforma a sus necesidades específicas sin complicaciones técnicas.

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Extensiones
- **`ListAvailableAddonsAction`**: Recupera el catálogo de complementos disponibles para el tenant, filtrando aquellos que ya están instalados o los que no son compatibles con su plan actual.
- **`InstallAddonAction`**: Inicia el proceso de instalación de un complemento, ejecutando las configuraciones iniciales necesarias (ej. creación de tablas específicas para el addon bajo el esquema Single-DB).
- **`ToggleAddonAction`**: Permite habilitar o deshabilitar un complemento instalado de forma instantánea.
- **`UninstallAddonAction`**: Remueve el complemento y limpia las configuraciones asociadas, preservando la integridad de los datos según la política de retención del addon.

## 3. Capa de Presentación (UI)

### 3.1 Componentes Livewire
- **`AddonMarketplace`**: Una tienda interna para los tenants donde pueden explorar, instalar y gestionar sus complementos con una interfaz visual intuitiva.
- **`AddonSettings`**: Proporciona interfaces de configuración específicas para cada complemento activo.

## 4. Seguridad y Autorización

### 4.1 Policies
- **`AddonPolicy`**: Restringe la instalación y desinstalación de complementos a usuarios con permisos de administración de sistema dentro del tenant.

## 5. Integración con Facturación
La activación de ciertos addons puede estar vinculada a cargos adicionales. El módulo se integra con `SelfServiceBilling` para procesar pagos o actualizaciones de suscripción antes de completar la instalación del complemento.

## 6. Consideraciones Técnicas
- El sistema utiliza **Feature Flags** (Laravel Pennant) internamente para controlar la visibilidad de las interfaces y la lógica de los addons activos.
- Los datos generados por los addons siempre incluyen el `tenant_id` para cumplir con la arquitectura Single-DB.
