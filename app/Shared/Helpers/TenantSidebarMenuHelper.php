<?php

declare(strict_types=1);

namespace App\Shared\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class TenantSidebarMenuHelper {
   /**
    * Obtiene la estructura completa del menú para el tenant actual.
    *
    * @return Collection<int, array<string, mixed>>
    */
   public static function getMenu(): Collection {
      return collect([
         self::platformSection(),
         self::identitySection(),
         self::operationsSection(),
         self::governanceSection(),
      ])->filter(fn($section) => ! empty($section['items']));
   }

   private static function platformSection(): array {
      return [
         'heading' => __('Plataforma'),
         'items' => self::filterItems([
            [
               'label'  => __('Dashboard'),
               'icon'   => 'home',
               'route'  => 'tenant.dashboard',
               'active' => Request::routeIs('tenant.dashboard'),
            ],
            [
               'label'  => __('Archivos'),
               'icon'   => 'paper-clip',
               'route'  => 'tenant.files.index',
               'active' => Request::routeIs('tenant.files.*'),
               'can'    => 'files.viewAny', // Ejemplo de permiso
            ],
            [
               'label'  => __('CSV Transfer'),
               'icon'   => 'arrow-path',
               'route'  => 'tenant.csv-transfer.index',
               'active' => Request::routeIs('tenant.csv-transfer.*'),
               'can'    => 'export-import.manage',
            ],
         ]),
      ];
   }

   private static function identitySection(): array {
      return [
         'heading' => __('Identidad'),
         'items' => self::filterItems([
            [
               'label'  => __('Usuarios'),
               'icon'   => 'users',
               'route'  => 'tenant.users.index',
               'active' => Request::routeIs('tenant.users.*'),
               'can'    => 'users.viewAny',
            ],
            [
               'label'  => __('API Access'),
               'icon'   => 'key',
               'active' => Request::routeIs('tenant.api.*'),
               'can'    => 'api.manage',
               'children' => [
                  [
                     'label' => __('Mi Perfil API'),
                     'route' => 'tenant.api.me.show',
                     'active' => Request::routeIs('tenant.api.me.show'),
                  ],
               ],
            ],
         ]),
      ];
   }

   private static function operationsSection(): array {
      return [
         'heading' => __('Operaciones'),
         'items' => self::filterItems([
            [
               'label'  => __('Notificaciones'),
               'icon'   => 'bell',
               'route'  => 'tenant.notifications.index',
               'active' => Request::routeIs('tenant.notifications.*'),
            ],
            [
               'label'  => __('Activity Log'),
               'icon'   => 'clipboard-document-list',
               'route'  => 'tenant.activity-log.index',
               'active' => Request::routeIs('tenant.activity-log.*'),
               'can'    => 'logs.viewAny',
            ],
            [
               'label'  => __('Analytics'),
               'icon'   => 'chart-bar',
               'route'  => 'tenant.analytics',
               'active' => Request::routeIs('tenant.analytics'),
               'can'    => 'reporting.view',
            ],
            [
               'label'  => __('Webhooks'),
               'icon'   => 'arrow-path-rounded-square',
               'active' => Request::routeIs('tenant.webhooks.*'),
               'can'    => 'webhooks.manage',
               'children' => [
                  [
                     'label' => __('Endpoints'),
                     'route' => 'tenant.webhooks.outgoing',
                     'active' => Request::routeIs('tenant.webhooks.outgoing'),
                  ],
                  [
                     'label' => __('Entrantes'),
                     'route' => 'tenant.webhooks.incoming',
                     'active' => Request::routeIs('tenant.webhooks.incoming'),
                  ],
               ],
            ],
         ]),
      ];
   }

   private static function governanceSection(): array {
      return [
         'heading' => __('Gestión'),
         'items' => self::filterItems([
            [
               'label'  => __('Configuración'),
               'icon'   => 'cog-6-tooth',
               'route'  => 'tenant.settings',
               'active' => Request::routeIs('tenant.settings'),
               'can'    => 'settings.manage',
            ],
            [
               'label'  => __('Facturación'),
               'icon'   => 'credit-card',
               'route'  => 'tenant.billing.portal',
               'active' => Request::routeIs('tenant.billing.*'),
            ],
            [
               'label'  => __('Mi Plan'),
               'icon'   => 'sparkles',
               'route'  => 'tenant.plan-features',
               'active' => Request::routeIs('tenant.plan-features'),
            ],
            [
               'label'  => __('Addons'),
               'icon'   => 'puzzle-piece',
               'route'  => 'tenant.addons',
               'active' => Request::routeIs('tenant.addons'),
               'can'    => 'addons.manage',
            ],
            [
               'label'  => __('Dominios'),
               'icon'   => 'globe-alt',
               'route'  => 'tenant.custom-domains',
               'active' => Request::routeIs('tenant.custom-domains'),
               'can'    => 'domains.manage',
            ],
         ]),
      ];
   }

   /**
    * Filtra los items del menú según los permisos del usuario actual.
    *
    * @param  array<int, array<string, mixed>>  $items
    * @return array<int, array<string, mixed>>
    */
   private static function filterItems(array $items): array {
      $filtered = [];

      foreach ($items as $item) {
         // Verificar permiso del item principal
         if (isset($item['can']) && ! Auth::guard('tenant')->user()?->can($item['can'])) {
            continue;
         }

         // Si tiene hijos, filtrarlos también
         if (isset($item['children'])) {
            $item['children'] = self::filterItems($item['children']);
            
            // Si después de filtrar no quedan hijos y el item dependía de ellos, podrías decidir ocultarlo
            // Pero usualmente los items con hijos tienen su propia lógica
            if (empty($item['children']) && !isset($item['route'])) {
                continue;
            }
         }

         $filtered[] = $item;
      }

      return $filtered;
   }
}
