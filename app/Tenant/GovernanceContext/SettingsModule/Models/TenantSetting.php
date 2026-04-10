<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SettingsModule\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantSetting extends Model {
   protected $table = 'tenant_settings';

   protected $fillable = [
      'tenant_id',
      'company_name',
      'legal_name',
      'support_email',
      'locale',
      'timezone',
      'currency',
      'branding',
      'preferences',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'branding' => 'array',
         'preferences' => 'array',
      ];
   }
}
