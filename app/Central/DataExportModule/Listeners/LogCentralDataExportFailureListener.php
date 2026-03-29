<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Listeners;

use App\Central\DataExportModule\Events\CentralDataExportFailed;
use Illuminate\Support\Facades\Log;

final class LogCentralDataExportFailureListener {
   public function handle(CentralDataExportFailed $event): void {
      Log::warning('Central GDPR export failed.', [
         'export_id' => $event->export->id,
         'tenant_id' => $event->export->tenant_id,
         'message' => $event->message,
      ]);
   }
}
