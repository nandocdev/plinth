<?php

use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/home', 'welcome');

Route::middleware(['auth:central', 'verified', EnsureSystemAdminHasTwoFactorEnabled::class])->group(function (): void {
    Route::redirect('dashboard', 'central/dashboard')->name('dashboard');
});

require __DIR__ . '/settings.php';
