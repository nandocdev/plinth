<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Models;

use App\Central\AuthenticationModule\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantImpersonationToken extends Model {
   protected $connection = 'central';
   
   protected $table = 'tenant_impersonation_tokens';

   protected $fillable = [
      'tenant_id',
      'impersonator_user_id',
      'target_domain',
      'token_hash',
      'expires_at',
      'used_at',
      'meta',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'expires_at' => 'datetime',
         'used_at' => 'datetime',
         'meta' => 'array',
      ];
   }

   public function tenant(): BelongsTo {
      return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
   }

   public function impersonator(): BelongsTo {
      return $this->belongsTo(User::class, 'impersonator_user_id');
   }
}
