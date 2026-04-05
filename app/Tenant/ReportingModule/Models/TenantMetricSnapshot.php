<?php

declare(strict_types=1);

namespace App\Tenant\ReportingModule\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantMetricSnapshot extends Model {
   protected $table = 'tenant_metric_snapshots';

   protected $fillable = [
      'snapshot_date',
      'metric_key',
      'value',
      'meta',
   ];

   /** @return array<string, string> */
   protected function casts(): array {
      return [
         'snapshot_date' => 'date',
         'value' => 'float',
         'meta' => 'array',
      ];
   }
}
