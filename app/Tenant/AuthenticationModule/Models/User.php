<?php

declare(strict_types=1);

namespace App\Tenant\AuthenticationModule\Models;

use App\Tenant\UserManagementModule\Enums\TenantUserStatus;
use Database\Factories\TenantUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable {
   /** @use HasFactory<TenantUserFactory> */
   use HasFactory;
   use HasRoles;
   use Notifiable;

   protected $table = 'users';

   protected string $guard_name = 'tenant';

   protected $fillable = [
      'name',
      'email',
      'password',
      'email_verified_at',
      'status',
   ];

   protected $hidden = [
      'password',
      'remember_token',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'email_verified_at' => 'datetime',
         'password'          => 'hashed',
         'status'            => TenantUserStatus::class,
      ];
   }

   public function isActive(): bool {
      return $this->status === TenantUserStatus::Active;
   }

   protected static function newFactory(): TenantUserFactory {
      return TenantUserFactory::new();
   }
}
