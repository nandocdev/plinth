<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LandingBlock extends Model {
   protected $table = 'landing_blocks';

   protected $fillable = [
      'tenant_landing_id',
      'block_type',
      'order',
      'is_active',
      'settings',
   ];

   protected function casts(): array {
      return [
         'is_active' => 'boolean',
         'settings' => 'array',
      ];
   }

   public function landing(): BelongsTo {
      return $this->belongsTo(TenantLanding::class, 'tenant_landing_id');
   }

   public function setting(string $key, mixed $default = null): mixed {
      $settings = is_array($this->settings) ? $this->settings : [];

      return $settings[$key] ?? $default;
   }
}
