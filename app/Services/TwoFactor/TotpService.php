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
     * Verify a submitted app code against the secret, allowing ±30s drift.
     */
    public function verify(string $secret, string $code): bool
    {
        return (bool) $this->google2fa->verifyKey($secret, $code, self::DRIFT_WINDOW);
    }
}
