# Tenant Module: Reporting (Análisis y Métricas del Cliente)

## 1. Propósito
El módulo **Reporting** proporciona a los administradores del tenant una visión clara del rendimiento de su organización. Proporciona dashboards visuales, resúmenes de métricas clave (KPIs) y herramientas para generar informes históricos basados exclusivamente en sus datos aislados.

## 2. Capa de Lógica (Acciones)

### 2.1 Generación de Métricas
- **`GetAnalyticsSummaryAction`**: Calcula los indicadores principales del tenant (ej. total de registros, usuarios activos en el último mes, uso de almacenamiento).
- **`GetMetricSeriesAction`**: Genera series de datos temporales para la construcción de gráficos (line charts, bar charts).
- **`TakeMetricSnapshotAction`**: Tarea programada que captura el estado del tenant en un momento dado para permitir el análisis de tendencias a largo plazo.

## 3. Capa de Presentación (UI)

### 3.1 Componentes Livewire
- **`TenantAnalyticsDashboard`**: Centro visual con tarjetas de métricas, gráficos interactivos y tablas de resumen.
- **`ReportGenerator`**: Herramienta para que el cliente configure y exporte reportes personalizados en formatos descargables.

## 4. Seguridad y Autorización

### 4.1 Policies
- **`ReportingPolicy`**: El acceso a los datos analíticos de la organización está restringido a roles directivos o administrativos (`reports.view`).

## 5. Consideraciones de Single-DB
- **Rendimiento de Consultas:** Dado que las tablas pueden contener millones de registros, este módulo utiliza técnicas de **agregación** y **caching** (Redis) para asegurar que los dashboards carguen instantáneamente.
- Las consultas siempre incluyen el Global Scope de `tenant_id`, garantizando que un cliente nunca vea métricas agregadas de la plataforma global o de otros clientes.

## 6. Consideraciones Técnicas
- El uso de `TakeMetricSnapshotAction` evita que el sistema tenga que realizar cálculos pesados de agregación cada vez que un usuario abre el dashboard, consultando en su lugar los snapshots pre-calculados.
