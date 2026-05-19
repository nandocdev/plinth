# Tenant Module: Custom Domain (Dominios Personalizados)

## 1. Propósito
El módulo **Custom Domain** permite a los clientes de Plinth utilizar su propia identidad de marca en la URL de acceso (ej. `app.mi-empresa.com` en lugar de `mi-empresa.plinth.com`). Gestiona la vinculación de dominios externos, la verificación de registros DNS y la provisión automática de certificados SSL (HTTPS).

## 2. Capa de Lógica (Acciones)

### 2.1 Gestión de Dominios
- **`CreateTenantCustomDomainAction`**: Registra una solicitud de dominio personalizado para el tenant actual.
- **`ListTenantCustomDomainsAction`**: Recupera todos los dominios vinculados al cliente y su estado actual (pendiente, verificado, error).
- **`RemoveTenantCustomDomainAction`**: Desvincula un dominio del espacio de trabajo del cliente.
- **`ToggleTenantCustomDomainVerificationAction`**: Activa el proceso de comprobación de registros CNAME/A en los DNS del cliente.
- **`RequestTenantDomainSslCertificateAction`**: Orquesta la solicitud de certificados SSL (ej. vía Let's Encrypt) para garantizar que el dominio personalizado sea seguro.

## 3. Capa de Presentación (UI)

### 3.1 Componentes Livewire
- **`DomainSettingsManager`**: Interfaz donde el cliente configura sus dominios, visualiza los registros DNS que debe añadir en su registrador y monitorea el estado de activación de sus URLs.

## 4. Seguridad y Aislamiento
- **Validación de Unicidad:** El sistema asegura que un dominio personalizado solo pueda estar vinculado a un único `tenant_id` en toda la plataforma global.
- **Aislamiento de Sesión:** Plinth gestiona las cookies de sesión de forma que funcionen correctamente tanto en el subdominio principal como en los dominios personalizados del cliente.

## 5. Consideraciones Técnicas
- El enrutamiento multi-tenant detecta el `Host` de la petición HTTP y busca el `tenant_id` correspondiente en la tabla global de dominios.
- Se recomienda el uso de **CDN** o **Proxies Inversos** (ej. Caddy, Nginx) configurados para interactuar con este módulo y gestionar los certificados SSL de forma dinámica.
