<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\FileUploadModule\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ListTenantUploadedFilesAction {
   public function execute(string $search, int $perPage, int $page): LengthAwarePaginator {
      $tenantId = $this->resolveTenantId();

      $query = DB::connection('central')
         ->table('tenant_uploaded_files')
         ->where('tenant_id', $tenantId)
         ->orderByDesc('created_at');

      $trimmedSearch = trim($search);

      if ($trimmedSearch !== '') {
         $lower = mb_strtolower($trimmedSearch);

         $query->where(function ($q) use ($lower): void {
            $q->whereRaw('LOWER(original_name) LIKE ?', ['%' . $lower . '%'])
               ->orWhereRaw("LOWER(COALESCE(mime_type, '')) LIKE ?", ['%' . $lower . '%'])
               ->orWhereRaw("LOWER(COALESCE(folder, '')) LIKE ?", ['%' . $lower . '%']);
         });
      }

      return $query->paginate(
         perPage: in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
         page: $page,
         pageName: 'page',
      );
   }

   private function resolveTenantId(): string {
      $tenantId = tenant()?->id;

      if (! is_string($tenantId) || $tenantId === '') {
         throw new \RuntimeException('Tenant context no inicializado para file uploads.');
      }

      return $tenantId;
   }
}
