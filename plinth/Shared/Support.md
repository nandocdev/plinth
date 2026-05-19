# Shared Context: Support (Soporte y Validaciones)

## 1. Propósito
El módulo **Support** centraliza reglas de validación, catálogos de preferencias y clases de apoyo que son utilizadas de forma repetitiva en diferentes formularios y procesos de la aplicación.

## 2. Componentes Clave

### 2.1 Reglas de Validación
- **`PasswordValidationRules`**: Estandariza la complejidad requerida para las contraseñas en todo el ecosistema (mínimo de caracteres, símbolos, etc.).
- **`ProfileValidationRules`**: Reglas comunes para la actualización de datos de perfil de usuario.

### 2.2 Catálogos
- **`TenantPreferenceCatalog`**: Define las opciones disponibles para configuraciones como zonas horarias, formatos de fecha y monedas soportadas por la plataforma.

### 2.3 Navegación
- Componentes base para la gestión de breadcrumbs y otros elementos de navegación compartidos entre el panel Central y el panel Tenant.

## 3. Consideraciones Técnicas
- Este módulo promueve el principio DRY (Don't Repeat Yourself), permitiendo que un cambio en una regla de negocio (ej. aumentar la longitud mínima de contraseña) se refleje instantáneamente en todos los módulos de autenticación de la plataforma.
