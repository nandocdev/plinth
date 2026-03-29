<?php

declare(strict_types=1);

namespace App\Tenant\AuthenticationModule\Models;

use Database\Factories\TenantUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable {
   /** @use HasFactory<TenantUserFactory> */
   use HasFactory;
   use Notifiable;

   protected $table = 'users';

   protected $fillable = [
      'name',
      'email',
      'password',
      'email_verified_at',
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
