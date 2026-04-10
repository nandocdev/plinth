<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\AddonsModule\Enums;

/**
 * Catálogo de addons disponibles para instalar en un tenant.
 * Representa la fuente de verdad de qué existe; el estado instalado/activo
 * vive en `tenant_addons` (tenant DB).
 */
enum AvailableAddon: string {
   case Analytics       = 'analytics';
   case Webhooks        = 'webhooks';
   case ApiAccess       = 'api_access';
   case FileUploads     = 'file_uploads';
   case ExportImport    = 'export_import';
   case ActivityLog     = 'activity_log';
   case Notifications   = 'notifications';

   public function label(): string {
      return match ($this) {
         self::Analytics     => 'Analytics avanzado',
         self::Webhooks      => 'Webhooks',
         self::ApiAccess     => 'Acceso API',
         self::FileUploads   => 'Carga de archivos',
         self::ExportImport  => 'Exportar / Importar',
         self::ActivityLog   => 'Activity Log',
         self::Notifications => 'Notificaciones',
      };
   }

   public function description(): string {
      return match ($this) {
         self::Analytics     => 'Dashboard de métricas diarias: usuarios, logins, webhooks y almacenamiento.',
         self::Webhooks      => 'Envía y recibe webhooks de eventos del tenant hacia servicios externos.',
         self::ApiAccess     => 'Tokens Sanctum para acceso programático a la API del tenant.',
         self::FileUploads   => 'Subida y gestión de archivos en el disco privado del tenant.',
         self::ExportImport  => 'Exporta e importa usuarios y datos vía CSV de forma asíncrona.',
         self::ActivityLog   => 'Registro de todas las acciones realizadas dentro del workspace.',
         self::Notifications => 'Notificaciones internas y por email para los usuarios del tenant.',
      };
   }

   public function icon(): string {
      return match ($this) {
         self::Analytics     => 'chart-bar',
         self::Webhooks      => 'arrow-path-rounded-square',
         self::ApiAccess     => 'key',
         self::FileUploads   => 'paper-clip',
         self::ExportImport  => 'arrows-up-down',
         self::ActivityLog   => 'clipboard-document-list',
         self::Notifications => 'bell',
      };
   }

   public function category(): AddonCategory {
      return match ($this) {
         self::Analytics                 => AddonCategory::Analytics,
         self::Webhooks, self::ApiAccess => AddonCategory::Integration,
         self::FileUploads,
         self::ExportImport              => AddonCategory::Productivity,
         self::ActivityLog,
         self::Notifications             => AddonCategory::Observability,
      };
   }

   /** @return list<string> */
   public static function values(): array {
      return array_column(self::cases(), 'value');
   }
}
