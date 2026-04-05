<?php

declare(strict_types=1);

namespace App\Tenant\WebhookModule\Enums;

enum TenantWebhookEvent: string {
   case UserCreated = 'user.created';
   case UserUpdated = 'user.updated';
   case UserDeleted = 'user.deleted';
   case SettingsUpdated = 'settings.updated';
   case FileUploaded = 'file.uploaded';
   case AddonInstalled = 'addon.installed';
   case AddonUninstalled = 'addon.uninstalled';

   public function label(): string {
      return match ($this) {
         self::UserCreated => 'Usuario creado',
         self::UserUpdated => 'Usuario actualizado',
         self::UserDeleted => 'Usuario eliminado',
         self::SettingsUpdated => 'Configuración actualizada',
         self::FileUploaded => 'Archivo subido',
         self::AddonInstalled => 'Addon instalado',
         self::AddonUninstalled => 'Addon desinstalado',
      };
   }

   /** @return list<string> */
   public static function values(): array {
      return array_map(static fn(self $event): string => $event->value, self::cases());
   }
}
