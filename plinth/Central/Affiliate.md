# Central Module: Affiliate (Sistema de Afiliados y Referidos)

## 1. Propósito
El módulo **Affiliate** permite la expansión del ecosistema Plinth mediante un sistema de socios (Partners) que refieren a nuevos tenants. Gestiona la creación de códigos de referidos, el seguimiento de conversiones y la atribución de nuevos clientes a sus respectivos promotores.

## 2. Arquitectura de Datos

### 2.1 Modelos Principales
- **`ReferralPartner`**: Representa a un socio afiliado. Contiene su información de contacto, código de referido único y estado (activo/inactivo).
- **`ReferralConversion`**: Registra cada vez que un nuevo tenant se crea utilizando un código de referido. Vincula al `ReferralPartner` con el `Tenant` resultante.

## 3. Capa de Lógica (Acciones)

### 3.1 Gestión de Partners
- **`ListReferralPartnersAction`**: Proporciona un listado filtrable de todos los socios registrados.
- **`CreateReferralPartnerAction`**: Registra un nuevo partner y genera su código de referido único.
- **`FindReferralPartnerByCodeAction`**: Busca un partner específico basado en su código de invitación (usado durante el registro de tenants).
- **`UpdateReferralPartnerStatusAction`**: Permite activar o suspender a un socio.

### 3.2 Gestión de Conversiones
- **`RegisterReferralConversionAction`**: Crea el vínculo formal entre un socio y un nuevo tenant una vez que el aprovisionamiento es exitoso.
- **`ListReferralConversionsAction`**: Muestra el historial de registros exitosos atribuidos a los diferentes socios.

## 4. Efectos Secundarios (Listeners)
- **`RegisterReferralConversionOnTenantCreated`**: Este listener reacciona al evento `TenantCreatedFromCentral`. Si existe una cookie o parámetro de referido válido durante la creación, dispara automáticamente la acción de registro de conversión.

## 5. Capa de Presentación (UI)

### 5.1 Componentes Livewire
- **`PartnerManagement`**: Interfaz administrativa para gestionar la lista de socios y sus estados.
- **`ReferralConversions`**: Tablero de visualización de conversiones exitosas.
- **`AffiliateCrud`**: Componente maestro para la administración de todo el sistema de referidos.

### 5.2 Formularios
- **`ReferralPartnerForm`**: Encapsula la lógica de validación para la creación y edición de socios (nombre, email, código, etc.).

## 6. Seguridad y Autorización

### 6.1 Policies
- **`ReferralPartnerPolicy`**: Protege las acciones de gestión de socios (`central.affiliates.manage`).
- **`ReferralConversionPolicy`**: Restringe la visualización de datos de conversión (`central.affiliates.view`).

### 6.2 Rutas
- Las rutas están agrupadas bajo el prefijo `central/affiliates` y requieren autenticación en el guard `central` y permisos específicos.

## 7. Consideraciones Técnicas
- El sistema utiliza códigos alfanuméricos únicos para la identificación de socios en lugar de IDs numéricos en las URLs de invitación para mayor seguridad y estética.
- Las conversiones son inmutables: una vez que un tenant es atribuido a un socio, el registro persiste para fines de auditoría histórica.
