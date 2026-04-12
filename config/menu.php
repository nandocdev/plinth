<?php

/**
 * Definición de la estructura del menú lateral para el tenant.
 * Cada entrada raíz es un grupo desplegable (heading) con sus items en 'children'.
 * [
 *    'label'    => 'Plataforma',
 *    'icon'     => 'rectangle-group',
 *    'children' => [
 *       [
 *          'label'    => 'Usuarios',
 *          'icon'     => 'users',
 *          'route'    => 'users.index',          // opcional
 *          'active'   => 'users.*',              // string  → Request::routeIs()
 *                                                // callable fn(): bool
 *          'can'      => 'users.viewAny',        // string  → $user->can()
 *                                                // array   → ['ability'=>'...','model'=>'...']
 *                                                // callable fn(?Authenticatable $user): bool
 *          'children' => [...],                  // opcional — sub-grupo
 *       ],
 *    ],
 * ]
 */

return [
   [
      'label' => 'Plataforma',
      'icon'  => 'rectangle-group',
      'children' => [
         [
            'label'  => 'Dashboard',
            'icon'   => 'home',
            'route'  => 'tenant.dashboard',
            'active' => 'tenant.dashboard',
         ],
         [
            'label'  => 'Archivos',
            'icon'   => 'paper-clip',
            'route'  => 'tenant.files.index',
            'active' => 'tenant.files.*',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\PlatformContext\FileUploadModule\Models\TenantUploadedFile::class,
            ],
         ],
         [
            'label'  => 'CSV Transfer',
            'icon'   => 'arrow-path',
            'route'  => 'tenant.csv-transfer.index',
            'active' => 'tenant.csv-transfer.*',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\PlatformContext\ExportImportModule\Models\TenantCsvTransferRun::class,
            ],
         ],
      ],
   ],
   [
      'label' => 'Identidad',
      'icon'  => 'user-group',
      'children' => [
         [
            'label'  => 'Usuarios',
            'icon'   => 'users',
            'route'  => 'tenant.users.index',
            'active' => 'tenant.users.*',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\IdentityContext\AuthenticationModule\Models\User::class,
            ],
         ],
         [
            'label'  => 'Roles',
            'icon'   => 'shield-check',
            'route'  => 'tenant.roles.index',
            'active' => 'tenant.roles.*',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \Spatie\Permission\Models\Role::class,
            ],
         ],
         [
            'label'    => 'API Access',
            'icon'     => 'key',
            'active'   => 'tenant.api.*',
            'can'      => 'tenant.api.view-self',
            'children' => [
               [
                  'label'  => 'Mi Perfil API',
                  'route'  => 'tenant.api.me.show',
                  'active' => 'tenant.api.me.show',
                  'can'    => 'tenant.api.view-self',
               ],
            ],
         ],
      ],
   ],
   [
      'label' => 'Operaciones',
      'icon'  => 'bolt',
      'children' => [
         [
            'label'  => 'Notificaciones',
            'icon'   => 'bell',
            'route'  => 'tenant.notifications.index',
            'active' => 'tenant.notifications.*',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\OperationsContext\NotificationModule\Models\TenantNotification::class,
            ],
         ],
         [
            'label'  => 'Activity Log',
            'icon'   => 'clipboard-document-list',
            'route'  => 'tenant.activity-log.index',
            'active' => 'tenant.activity-log.*',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\OperationsContext\ActivityLogModule\Models\TenantActivityLogEntry::class,
            ],
         ],
         [
            'label'  => 'Analytics',
            'icon'   => 'chart-bar',
            'route'  => 'tenant.analytics',
            'active' => 'tenant.analytics',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\OperationsContext\ReportingModule\Models\TenantMetricSnapshot::class,
            ],
         ],
         [
            'label'    => 'Webhooks',
            'icon'     => 'arrow-path-rounded-square',
            'active'   => 'tenant.webhooks.*',
            'children' => [
               [
                  'label'  => 'Endpoints',
                  'route'  => 'tenant.webhooks.outgoing',
                  'active' => 'tenant.webhooks.outgoing',
                  'can'    => [
                     'ability' => 'viewAny',
                     'model'   => \App\Tenant\OperationsContext\WebhookModule\Models\WebhookEndpoint::class,
                  ],
               ],
               [
                  'label'  => 'Entrantes',
                  'route'  => 'tenant.webhooks.incoming',
                  'active' => 'tenant.webhooks.incoming',
                  'can'    => [
                     'ability' => 'viewAny',
                     'model'   => \App\Tenant\OperationsContext\WebhookModule\Models\IncomingWebhookToken::class,
                  ],
               ],
            ],
         ],
      ],
   ],
   [
      'label' => 'Gestión',
      'icon'  => 'wrench-screwdriver',
      'children' => [
         [
            'label'  => 'Configuración',
            'icon'   => 'cog-6-tooth',
            'route'  => 'tenant.settings',
            'active' => 'tenant.settings',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\GovernanceContext\SettingsModule\Models\TenantSetting::class,
            ],
         ],
         [
            'label'  => 'Landing Builder',
            'icon'   => 'paint-brush',
            'route'  => 'tenant.landing.builder',
            'active' => 'tenant.landing.*',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\GovernanceContext\LandingBuilderModule\Models\TenantLanding::class,
            ],
         ],
         [
            'label'  => 'Facturación',
            'icon'   => 'credit-card',
            'route'  => 'tenant.billing.portal',
            'active' => 'tenant.billing.*',
         ],
         [
            'label'  => 'Mi Plan',
            'icon'   => 'sparkles',
            'route'  => 'tenant.plan-features',
            'active' => 'tenant.plan-features',
         ],
         [
            'label'  => 'Addons',
            'icon'   => 'puzzle-piece',
            'route'  => 'tenant.addons',
            'active' => 'tenant.addons',
            'can'    => [
               'ability' => 'viewAny',
               'model'   => \App\Tenant\GovernanceContext\AddonsModule\Models\TenantAddon::class,
            ],
         ],
         [
            'label'  => 'Dominios',
            'icon'   => 'globe-alt',
            'route'  => 'tenant.custom-domains',
            'active' => 'tenant.custom-domains',
            'can'    => 'tenant.custom-domains.view',
         ],
      ],
   ],
];
