<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Listeners;

use App\Central\DataExportModule\Events\CentralDataExportCompleted;
use Illuminate\Support\Facades\Log;

final class LogCentralDataExportCompletionListener {
   public function handle(CentralDataExportCompleted $event): void {
      Log::info('Central GDPR export completed.', [
         'export_id' => $event->export->id,
         'tenant_id' => $event->export->tenant_id,
         'file_path' => $event->export->file_path,
      ]);
   }
}