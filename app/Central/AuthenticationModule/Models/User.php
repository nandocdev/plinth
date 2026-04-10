<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
final class User extends Authenticatable {
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /** Spatie usa este guard para resolver roles/permisos en la BD central */
    protected string $guard_name = 'central';

    protected static function newFactory(): UserFactory {
        return UserFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the tenant owned by this user, if any.
     */
    public function ownedTenant(): ?\App\Central\TenantProvisioningModule\Models\Tenant {
        /** @var \App\Central\TenantProvisioningModule\Models\Tenant|null $tenant */
        $tenant = \App\Central\TenantProvisioningModule\Models\Tenant::query()
            ->where('data->owner_system_admin_id', $this->id)
            ->first();

        return $tenant;
    }
}
