<?php

namespace App\Services\TwoFactor;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

    /**
     * Issue a fresh code, overwriting any active one, and email it.
     */
    public function sendCode(User $user): void
    {
        $code = $this->generateCode();

        $user->forceFill([
            'two_factor_code_hash' => Hash::make($code),
            'two_factor_code_expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ])->save();

        Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
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
        ])->save();

        return true;
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
