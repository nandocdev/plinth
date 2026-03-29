<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Http\Controllers;

use App\Central\DataExportModule\Models\CentralDataExport;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadCentralDataExportController extends Controller {
   use AuthorizesRequests;

   public function __invoke(CentralDataExport $export): BinaryFileResponse {
      $this->authorize('download', $export);

      $path = (string) $export->file_path;

      abort_unless($path !== '' && is_file($path), 404);

      return response()->download($path, basename($path));
   }
}