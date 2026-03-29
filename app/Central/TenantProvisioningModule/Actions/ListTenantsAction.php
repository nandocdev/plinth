<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTenantsAction {
   public function execute(string $search = '', int $perPage = 15): LengthAwarePaginator {
      $query = Tenant::query()
         ->with([
            'domains',
            'subscription.plan:id,name,slug',
         ])
         ->withCount('domains')
         ->latest('created_at');

      if ($search !== '') {
         $query->where(function ($builder) use ($search): void {
            $builder
               ->where('id', 'like', "%{$search}%")
               ->orWhere('data->name', 'like', "%{$search}%");
         });
      }

      return $query->paginate($perPage);
   }
}
