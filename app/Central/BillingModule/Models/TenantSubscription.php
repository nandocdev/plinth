<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Models;

use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantSubscription extends Model {
   protected $table = 'tenant_subscriptions';

   protected $fillable = [
      'tenant_id',
      'plan_id',
      'billing_period',
      'status',
      'trial_ends_at',
      'starts_at',
      'ends_at',
      'price_snapshot_cents',
      'external_id',
      'meta',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'trial_ends_at' => 'datetime',
         'starts_at' => 'datetime',
         'ends_at' => 'datetime',
         'meta' => 'array',
         'price_snapshot_cents' => 'integer',
      ];
   }

   public function tenant(): BelongsTo {
      return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
   }

   public function plan(): BelongsTo {
      return $this->belongsTo(Plan::class, 'plan_id');
   }
}
