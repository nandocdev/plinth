<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class Plan extends Model {
   use HasFactory;

   protected $table = 'plans';

   protected $fillable = [
      'name',
      'slug',
      'price_monthly_cents',
      'price_yearly_cents',
      'trial_days',
      'features',
      'max_users_soft',
      'max_users_hard',
      'max_storage_mb_soft',
      'max_storage_mb_hard',
      'is_active',
      'sort_order',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'features' => 'array',
         'is_active' => 'boolean',
         'price_monthly_cents' => 'integer',
         'price_yearly_cents' => 'integer',
         'trial_days' => 'integer',
         'max_users_soft' => 'integer',
         'max_users_hard' => 'integer',
         'max_storage_mb_soft' => 'integer',
         'max_storage_mb_hard' => 'integer',
         'sort_order' => 'integer',
      ];
   }

   public function subscriptions(): HasMany {
      return $this->hasMany(TenantSubscription::class, 'plan_id');
   }
}
