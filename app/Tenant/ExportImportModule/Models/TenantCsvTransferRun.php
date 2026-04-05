<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantCsvTransferRun extends Model {
   public const TYPE_EXPORT = 'export';
   public const TYPE_IMPORT = 'import';

   public const STATUS_PENDING = 'pending';
   public const STATUS_RUNNING = 'running';
   public const STATUS_COMPLETED = 'completed';
   public const STATUS_FAILED = 'failed';

   protected $table = 'tenant_csv_transfer_runs';

   protected $guarded = [];

   protected function casts(): array {
      return [
         'total_rows' => 'integer',
         'processed_rows' => 'integer',
         'started_at' => 'datetime',
         'completed_at' => 'datetime',
         'meta' => 'array',
      ];
   }
}
