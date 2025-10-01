<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MeController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\Auth\{
    AuthenticatedSessionController,
    RegisteredUserController,
    EmailVerificationNotificationController,
    VerifyEmailController,
    PasswordResetLinkController,
    NewPasswordController,
    SocialGoogleController,
};

Route::prefix('/v1')->group(function () {
    Route::prefix('auth')
        ->middleware(['web'])
        ->withoutMiddleware([
            'api',
            VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ])
        ->as('api.')
        ->group(function () {
        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('login')
            ->middleware('throttle:5,1');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout')
            ->middleware('auth:sanctum');

        Route::post('register', [RegisteredUserController::class, 'store'])
            ->name('register');

        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['auth:sanctum', 'throttle:6,1']);

        Route::get('email/verify', VerifyEmailController::class)
            ->name('verification.verify')
            ->middleware('signed');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');

        Route::get('google/redirect', [SocialGoogleController::class, 'redirect'])
            ->name('google.redirect');

        Route::get('google/callback', [SocialGoogleController::class, 'callback'])
            ->name('google.callback');
    });

    Route::get('health', fn () => response()->json(['ook' => true]))->name('api.health');

    Route::middleware(['web', 'auth:sanctum'])->group(function () {
        Route::get('me', MeController::class)->name('api.me');
        // ... autres endpoints protégés
    });
});
