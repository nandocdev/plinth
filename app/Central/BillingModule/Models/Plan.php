<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Plan extends Model {
   protected $table = 'plans';

   protected $fillable = [
      'name',
      'slug',
      'price_monthly_cents',
      'price_yearly_cents',
      'trial_days',
      'features',
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
         'sort_order' => 'integer',
      ];
   }

   public function subscriptions(): HasMany {
      return $this->hasMany(TenantSubscription::class, 'plan_id');
   }
}
