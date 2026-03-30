<?php

use App\Central\AuthenticationModule\Http\Middleware\EnsureSystemAdminHasTwoFactorEnabled;
use App\Central\BillingModule\Actions\GetPublicPlansAction;
use Illuminate\Support\Facades\Route;

Route::get('/', function (GetPublicPlansAction $action) {
    return view('welcome', ['plans' => $action->execute()]);
})->name('home');

Route::view('/home', 'welcome');

Route::middleware(['auth:central', 'verified', EnsureSystemAdminHasTwoFactorEnabled::class])->group(function (): void {
    Route::redirect('dashboard', 'central/dashboard')->name('dashboard');
});

require __DIR__ . '/settings.php';
