<?php

namespace App\Services\TwoFactor;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Generates, delivers, and verifies the emailed 6-digit second factor.
 *
 * The same machinery backs three surfaces: settings enrollment, the login
 * challenge, and the TOTP email fallback — all share one active code per user
 * plus the attempt/lockout counters on the users table.
 */
class TwoFactorCodeService
{
    /** Minutes an emailed code stays valid. */
    public const CODE_TTL_MINUTES = 10;

    /** Emailed codes allowed per rolling window. */
    public const MAX_SENDS = 3;

    /** Length of the send window, in seconds. */
    public const SEND_DECAY_SECONDS = 600;

    /** Failed challenge attempts tolerated before the account locks. */
    public const MAX_ATTEMPTS = 5;

    /** Minutes an account stays locked after too many failures. */
    public const LOCKOUT_MINUTES = 15;

    /**
     * Issue a fresh code, overwriting any active one, and email it. Every send
     * hits the per-user send limiter (see tooManyRecentSends()).
     */
    public function sendCode(User $user): void
    {
        RateLimiter::hit($this->sendKey($user), self::SEND_DECAY_SECONDS);

        $code = $this->generateCode();

        $user->forceFill([
            'two_factor_code_hash' => Hash::make($code),
            'two_factor_code_expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ])->save();

        Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
    }

    /**
     * Whether the user has hit the send cap for the current window. Callers
     * that are user-initiated (settings enroll/disable, challenge resend) check
     * this before sending; the automatic login-divert send does not.
     */
    public function tooManyRecentSends(User $user): bool
    {
        return RateLimiter::tooManyAttempts($this->sendKey($user), self::MAX_SENDS);
    }

    /**
     * Whether the account is currently locked out of the challenge.
     */
    public function isLocked(User $user): bool
    {
        return $user->two_factor_locked_until !== null && $user->two_factor_locked_until->isFuture();
    }

    /**
     * Record a failed challenge attempt, locking the account once the ceiling
     * is reached.
     */
    public function recordFailedAttempt(User $user): void
    {
        $attempts = $user->two_factor_attempts + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $user->forceFill([
                'two_factor_attempts' => 0,
                'two_factor_locked_until' => now()->addMinutes(self::LOCKOUT_MINUTES),
            ])->save();

            return;
        }

        $user->forceFill(['two_factor_attempts' => $attempts])->save();
    }

    private function sendKey(User $user): string
    {
        return 'two-factor:send:'.$user->id;
    }

    /**
     * Verify a submitted code against the active one. On success the code is
     * consumed and the attempt counter reset; on failure nothing is mutated
     * here (attempt/lockout accounting is the caller's concern).
     */
    public function verifyCode(User $user, string $code): bool
    {
        if ($user->two_factor_code_hash === null || $user->two_factor_code_expires_at === null) {
            return false;
        }

        if ($user->two_factor_code_expires_at->isPast()) {
            return false;
        }

        if (! Hash::check($code, $user->two_factor_code_hash)) {
            return false;
        }

        $user->forceFill([
            'two_factor_code_hash' => null,
            'two_factor_code_expires_at' => null,
            'two_factor_attempts' => 0,
            'two_factor_locked_until' => null,
        ])->save();

        return true;
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
