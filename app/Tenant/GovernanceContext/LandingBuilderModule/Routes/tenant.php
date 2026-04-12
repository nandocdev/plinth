<?php

declare(strict_types=1);

use App\Tenant\GovernanceContext\LandingBuilderModule\Http\Controllers\PublicLandingController;
use App\Tenant\GovernanceContext\LandingBuilderModule\Livewire\LandingBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/landing', PublicLandingController::class)->name('tenant.landing.public');

Route::middleware(['auth:tenant'])->group(function (): void {
   Route::get('/settings/landing-builder', LandingBuilder::class)->name('tenant.landing.builder');
   Route::get('/settings/landing-preview', PublicLandingController::class)->name('tenant.landing.preview');
});
