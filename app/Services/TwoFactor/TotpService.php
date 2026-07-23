<?php

namespace App\Services\TwoFactor;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

/**
 * Authenticator-app (TOTP) secret generation and verification. Wraps
 * pragmarx/google2fa; the QR itself is rendered from otpauthUri() by the
 * already-shipped SimpleSoftwareIO QrCode facade in the view.
 */
class TotpService
{
    /**
     * Verification window: 1 step each side of now, i.e. ±30s of clock drift.
     */
    public const DRIFT_WINDOW = 1;

    public function __construct(private readonly Google2FA $google2fa)
    {
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * The otpauth:// URI an authenticator app consumes (via QR or manual entry).
     */
    public function otpauthUri(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret,
        );
    }

    /**
     * Verify a submitted app code against the user's secret, allowing ±30s
     * drift, and reject replays: a code is accepted only if its time-step is
     * newer than the last accepted one, which is then recorded.
     */
    public function verify(User $user, string $code): bool
    {
        $secret = (string) $user->two_factor_totp_secret;

        if ($secret === '') {
            return false;
        }

        // A non-null oldTimestamp (0 on first use) is required so google2fa
        // returns the matched time-step rather than a bare `true` — otherwise
        // the recorded step is useless and replays slip through.
        $timestamp = $this->google2fa->verifyKeyNewer(
            $secret,
            $code,
            $user->two_factor_totp_last_timestamp ?? 0,
            self::DRIFT_WINDOW,
        );

        if ($timestamp === false) {
            return false;
        }

        $user->forceFill(['two_factor_totp_last_timestamp' => $timestamp])->save();

        return true;
    }
}
