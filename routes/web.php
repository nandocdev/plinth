<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('/home', 'welcome')->name('home');

Route::middleware(['auth:central', 'verified'])->group(function (): void {
    Route::redirect('dashboard', 'central/dashboard')->name('dashboard');
});

require __DIR__ . '/settings.php';
