<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\{
    AuthenticatedSessionController,
    RegisteredUserController,
    EmailVerificationNotificationController,
    VerifyEmailController,
    PasswordResetLinkController,
    NewPasswordController,
};

Route::prefix('/v1')->group(function () {
    Route::prefix('auth')->group(function () {
        // Auth SPA : web (session + CSRF)
        Route::post('login',  [AuthenticatedSessionController::class, 'store'])->name('api.login')
            ->middleware('throttle:5,1');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('api.logout');

        Route::post('register', [RegisteredUserController::class, 'store'])->name('api.register');

        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['auth:sanctum','throttle:6,1']);
        Route::get('email/verify', VerifyEmailController::class)
            ->name('api.verification.verify')
            ->middleware('signed');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('api.password.email');
        Route::post('reset-password',  [NewPasswordController::class, 'store'])->name('api.password.store');
    });
});


Route::prefix('/v1')->group(function () {
    Route::get('health', fn () => response()->json(['ok' => true]))->name('api.health');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', MeController::class)->name('api.me');
        // ... autres endpoints protégés
    });
});
