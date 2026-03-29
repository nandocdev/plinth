<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Actions;

use App\Central\DataExportModule\Models\CentralDataExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListCentralDataExportsAction {
   public function execute(int $perPage = 10): LengthAwarePaginator {
      return CentralDataExport::query()
         ->with(['tenant', 'requestedBy'])
         ->latest('created_at')
         ->paginate($perPage);
   }
}
