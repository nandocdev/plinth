# Central Module: System Health (Monitoreo de Plataforma)

## 1. Propósito
El módulo **System Health** proporciona una visión en tiempo real del estado técnico y operativo de todo el ecosistema Plinth. Su objetivo es permitir que los administradores identifiquen proactivamente cuellos de botella, fallos en servicios externos o degradación del rendimiento en el entorno compartido Single-DB.

## 2. Arquitectura de Monitoreo

### 2.1 Modelos Principales
- **`SystemHealthSnapshot`**: Representa una captura puntual del estado del sistema. Aunque es un modelo Eloquent, se utiliza principalmente para persistir reportes de salud históricos y tendencias de rendimiento.

## 3. Capa de Lógica (Acciones)

### 3.1 Recolección de Métricas
- **`BuildSystemHealthSnapshotAction`**: Ejecuta una serie de "Checkers" para validar:
    - Conectividad con la Base de Datos.
    - Estado y latencia de Redis.
    - Disponibilidad de servicios externos (dLocal, SMTP).
    - Espacio en disco y uso de memoria.
- **`BuildCentralAggregateMetricsAction`**: Agrega datos de negocio críticos para el dashboard central (ej. número de tenants activos, ingresos del día, tasa de éxito de webhooks).
- **`FilterSystemHealthSnapshotAction`**: Permite consultar el historial de estados para análisis post-mortem.

## 4. Capa de Presentación (UI)

### 4.1 Componentes Livewire
- **`SystemHealthDashboard`**: Centro de control visual que muestra indicadores clave de rendimiento (KPIs), estados de servicios (UP/DOWN) y alertas recientes del sistema. Utiliza componentes reactivos para actualizaciones en vivo sin recargar la página.

## 5. Integración con Laravel Pulse
El módulo complementa la funcionalidad de **Laravel Pulse**, enfocándose en métricas de nivel de negocio y salud de infraestructura de alto nivel, mientras que Pulse se encarga del monitoreo detallado de queries y jobs individuales.

## 6. Seguridad y Autorización

### 6.1 Policies
- **`SystemHealthSnapshotPolicy`**: El acceso a los datos de salud del sistema está restringido exclusivamente a administradores con el rol de `SuperAdmin` o `SupportAdmin` (`central.health.view`).

## 7. Consideraciones Técnicas
- Los chequeos de salud están diseñados para ser extremadamente ligeros, evitando que el propio proceso de monitoreo degrade el rendimiento de la plataforma.
- Se recomienda la ejecución periódica de `BuildSystemHealthSnapshotAction` mediante el Scheduler de Laravel para mantener un histórico de disponibilidad (Uptime).
