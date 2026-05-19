# Tenant Module: Feature Flags (Control de Funcionalidades y Límites)

## 1. Propósito
El módulo **Feature Flags** permite habilitar o deshabilitar dinámicamente capacidades de la plataforma para un tenant específico, basándose en su plan contratado o en configuraciones manuales de soporte. Además, gestiona los límites de uso (cuotas) para asegurar que el tenant opere dentro de los parámetros de su suscripción.

## 2. Arquitectura de Control (Pennant)
Plinth utiliza **Laravel Pennant** como motor para la gestión de características. Las flags están vinculadas al modelo `Tenant`, permitiendo cambios en tiempo real sin desplegar código.

## 3. Capa de Lógica (Acciones)

### 3.1 Evaluación de Capacidades
- **`CheckTenantHasFeatureAction`**: Valida si una funcionalidad específica (ej. `api-access`, `custom-branding`) está disponible para el cliente actual.
- **`GetTenantPlanFeaturesAction`**: Recupera la lista completa de características habilitadas según el plan comercial vinculado al tenant.
- **`EvaluateTenantUsageLimitsAction`**: Comprueba si el tenant ha alcanzado sus límites de uso (ej. número máximo de usuarios, espacio de almacenamiento, envíos de webhooks) antes de permitir una acción.

## 4. Gestión de Cuotas (Límites)
El sistema define límites granulares por plan:
- **Límites Estrictos:** Bloquean la acción (ej. no se pueden crear más usuarios).
- **Límites Soft:** Permiten la acción pero generan alertas administrativas para proponer un upgrade de plan.

## 5. Integración con el Código
Las comprobaciones se realizan de forma fluida en controladores y componentes Livewire mediante directivas de Blade o métodos de ayuda:
```php
if (Feature::active('advanced-reporting')) { ... }
```

## 6. Consideraciones Técnicas
- El estado de las Feature Flags se cachea en Redis para evitar consultas repetitivas a la base de datos durante la navegación del usuario.
- Los límites de uso se recalculan periódicamente o ante eventos específicos (ej. creación de un nuevo recurso) para mantener la precisión de las cuotas.
