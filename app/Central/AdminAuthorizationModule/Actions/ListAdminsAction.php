<?php

declare(strict_types=1);

namespace App\Central\AdminAuthorizationModule\Actions;

use App\Central\AuthenticationModule\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListAdminsAction {
   public function execute(?string $search = null, int $perPage = 20): LengthAwarePaginator {
      return User::query()
         ->with('roles')
         ->when(
            $search,
            fn($q) => $q->where(function ($q) use ($search): void {
               $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . mb_strtolower($search) . '%']);
            })
         )
         ->orderBy('name')
         ->paginate($perPage);
   }
}
