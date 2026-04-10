<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\FileUploadModule\Actions;

use App\Tenant\PlatformContext\FileUploadModule\DTOs\StoreTenantUploadedFileData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class StoreTenantUploadedFileAction {
   /**
    * @return array<string, mixed>
    */
   public function execute(StoreTenantUploadedFileData $data): array {
      $tenantId = $this->resolveTenantId();
      $folder = $this->normalizeFolder($data->folder);
      $timestampFolder = now()->format('Y/m');
      $baseFolder = $folder !== null ? $folder . '/' . $timestampFolder : $timestampFolder;

      $extension = $data->file->getClientOriginalExtension();
      $storedName = Str::uuid()->toString() . ($extension !== '' ? '.' . strtolower($extension) : '');
      $storedPath = trim($baseFolder . '/' . $storedName, '/');

      Storage::disk('tenant')->putFileAs($baseFolder, $data->file, $storedName);

      return DB::transaction(function () use ($tenantId, $data, $folder, $storedName, $storedPath): array {
         $id = DB::connection('central')
            ->table('tenant_uploaded_files')
            ->insertGetId([
               'tenant_id' => $tenantId,
               'uploaded_by_user_id' => $data->uploadedByUserId,
               'disk' => 'tenant',
               'folder' => $folder,
               'original_name' => $data->file->getClientOriginalName(),
               'stored_name' => $storedName,
               'stored_path' => $storedPath,
               'mime_type' => $data->file->getClientMimeType(),
               'size_bytes' => $data->file->getSize() ?? 0,
               'created_at' => now(),
               'updated_at' => now(),
            ]);

         $record = DB::connection('central')
            ->table('tenant_uploaded_files')
            ->where('id', $id)
            ->first();

         if (! is_object($record)) {
            throw new \RuntimeException('No fue posible leer metadata del archivo subido.');
         }

         /** @var array<string, mixed> $row */
         $row = (array) $record;

         return $row;
      });
   }

   private function resolveTenantId(): string {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para file uploads.');
      }

      return $tenantId;
   }

   private function normalizeFolder(?string $folder): ?string {
      if ($folder === null) {
         return null;
      }

      $trimmed = trim($folder);

      if ($trimmed === '') {
         return null;
      }

      return str_replace(['..', '\\\\'], '', trim($trimmed, '/'));
   }
}
