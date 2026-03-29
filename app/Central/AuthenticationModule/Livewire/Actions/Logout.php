<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class Logout {
    /**
     * Log the current user out of the application.
     */
    public function __invoke() {
        Auth::guard('central')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
