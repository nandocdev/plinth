<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\ReportingModule\Enums;

enum TenantMetricKey: string {
   case UsersTotal = 'users_total';
   case UsersActiveDay = 'users_active_day';
   case UsersActiveMonth = 'users_active_month';
   case LoginsDay = 'logins_day';
   case FilesUploaded = 'files_uploaded';
   case FilesStorageMb = 'files_storage_mb';
   case ActivityLogEntries = 'activity_log_entries';
   case WebhookDeliveriesSuccess = 'webhook_deliveries_success';
   case WebhookDeliveriesFailed = 'webhook_deliveries_failed';
   case ApiRequestsDay = 'api_requests_day';

   public function label(): string {
      return match ($this) {
         self::UsersTotal => 'Usuarios totales',
         self::UsersActiveDay => 'Usuarios activos hoy',
         self::UsersActiveMonth => 'Usuarios activos este mes',
         self::LoginsDay => 'Logins del día',
         self::FilesUploaded => 'Archivos subidos',
         self::FilesStorageMb => 'Almacenamiento total (MB)',
         self::ActivityLogEntries => 'Entradas de auditoría',
         self::WebhookDeliveriesSuccess => 'Webhooks entregados',
         self::WebhookDeliveriesFailed => 'Webhooks fallidos',
         self::ApiRequestsDay => 'Requests API del día',
      };
   }

   public function unit(): string {
      return match ($this) {
         self::FilesStorageMb => 'MB',
         default => '',
      };
   }

   /** @return list<string> */
   public static function values(): array {
      return array_map(static fn(self $k): string => $k->value, self::cases());
   }
}
