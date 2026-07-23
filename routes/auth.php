<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorSettingsController;
use App\Http\Controllers\Auth\TwoFactorTotpController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // Second-factor challenge: the user has passed the password check but is not
    // yet logged in (pending id stashed in the session), so this stays guest-safe.
    Route::get('two-factor/challenge', [TwoFactorChallengeController::class, 'create'])
        ->name('two-factor.challenge');

    // Per-IP throttle backstops the per-user lockout against distributed guessing.
    Route::post('two-factor/challenge', [TwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('two-factor.challenge.store');

    Route::post('two-factor/challenge/resend', [TwoFactorChallengeController::class, 'resend'])
        ->middleware('throttle:10,1')
        ->name('two-factor.challenge.resend');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Email-2FA enrollment (settings side). Login-time verification is guest-safe
    // and lives in the challenge routes above.
    Route::post('two-factor/email/enroll', [TwoFactorSettingsController::class, 'enroll'])
        ->name('two-factor.email.enroll');

    Route::post('two-factor/email/confirm', [TwoFactorSettingsController::class, 'confirm'])
        ->name('two-factor.email.confirm');

    // Disabling requires a fresh re-verification code (§1.6).
    Route::post('two-factor/email/disable-request', [TwoFactorSettingsController::class, 'requestDisable'])
        ->name('two-factor.email.disable-request');

    Route::delete('two-factor/email', [TwoFactorSettingsController::class, 'disable'])
        ->name('two-factor.email.disable');

    // Authenticator-app (TOTP) enrollment + management (§4).
    Route::post('two-factor/totp/setup', [TwoFactorTotpController::class, 'setup'])
        ->name('two-factor.totp.setup');

    Route::get('two-factor/totp/setup', [TwoFactorTotpController::class, 'show'])
        ->name('two-factor.totp.setup.show');

    Route::post('two-factor/totp/confirm', [TwoFactorTotpController::class, 'confirm'])
        ->name('two-factor.totp.confirm');

    Route::delete('two-factor/totp', [TwoFactorTotpController::class, 'disable'])
        ->name('two-factor.totp.disable');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
