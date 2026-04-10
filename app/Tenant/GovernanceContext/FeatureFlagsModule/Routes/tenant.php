<?php

declare(strict_types=1);

use App\Tenant\GovernanceContext\FeatureFlagsModule\Livewire\PlanFeaturesOverview;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/plan-features', PlanFeaturesOverview::class)->name('tenant.plan-features');
});
