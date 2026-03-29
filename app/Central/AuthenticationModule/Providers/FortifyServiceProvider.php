<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Providers;

use App\Central\AuthenticationModule\Actions\AttemptSystemAdminLoginAction;
use App\Central\AuthenticationModule\Actions\CreateNewUser;
use App\Central\AuthenticationModule\Actions\ResetUserPassword;
use App\Central\AuthenticationModule\DTOs\SystemAdminLoginData;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

final class FortifyServiceProvider extends ServiceProvider {
    public function register(): void {
        //
    }

    public function boot(): void {
        $this->configureActions();
        $this->configureAuthentication();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    private function configureActions(): void {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    private function configureAuthentication(): void {
        Fortify::authenticateUsing(function (Request $request) {
            $data = SystemAdminLoginData::fromRequest($request);

            return app(AttemptSystemAdminLoginAction::class)->execute($data);
        });
    }

    private function configureViews(): void {
        Fortify::loginView(fn() => view('pages::auth.login'));
        Fortify::verifyEmailView(fn() => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn() => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn() => view('pages::auth.confirm-password'));
        Fortify::registerView(fn() => view('pages::auth.register'));
        Fortify::resetPasswordView(fn() => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn() => view('pages::auth.forgot-password'));
    }

    private function configureRateLimiting(): void {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by((string) $request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower((string) $request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
