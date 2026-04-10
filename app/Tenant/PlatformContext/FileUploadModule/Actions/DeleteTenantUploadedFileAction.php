<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\FileUploadModule\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteTenantUploadedFileAction {
   public function execute(int $fileId): void {
      $tenantId = $this->resolveTenantId();

      DB::transaction(function () use ($fileId, $tenantId): void {
         $file = DB::connection('central')
            ->table('tenant_uploaded_files')
            ->where('id', $fileId)
            ->where('tenant_id', $tenantId)
            ->first();

         if (! is_object($file)) {
            throw new \RuntimeException('Archivo no encontrado para este tenant.');
         }

         Storage::disk((string) $file->disk)->delete((string) $file->stored_path);

         DB::connection('central')
            ->table('tenant_uploaded_files')
            ->where('id', $fileId)
            ->where('tenant_id', $tenantId)
            ->delete();
      });
   }

   private function resolveTenantId(): string {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para file uploads.');
      }

      return $tenantId;
   }
}
