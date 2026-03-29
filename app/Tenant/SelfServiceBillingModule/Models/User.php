<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Models;

use Database\Factories\TenantUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo de usuario en contexto tenant.
 * Usa la conexión por defecto (tenant DB) que stancl/tenancy inicializa al resolver el dominio.
 */
final class User extends Authenticatable {
   /** @use HasFactory<TenantUserFactory> */
   use HasFactory;
   use Notifiable;

   protected $table = 'users';

   protected $fillable = [
      'name',
      'email',
      'password',
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
         'password' => 'hashed',
      ];
   }

   protected static function newFactory(): TenantUserFactory {
      return TenantUserFactory::new();
   }
}
