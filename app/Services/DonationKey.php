<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * The configured donation key (#149). It arrives as operator env config rather
 * than through onboarding, so nothing validates it on the way in: `/donate` used
 * to register on truthiness alone and a malformed value only surfaced as a 500 at
 * the first donor. Everything here is config-only — no database — because the
 * route gate consults it on every request.
 */
class DonationKey
{
    public static function raw(): string
    {
        return trim((string) config('donations.xpub'));
    }

    public static function isConfigured(): bool
    {
        return self::raw() !== '';
    }

    public static function isUsable(): bool
    {
        return self::problem() === null;
    }

    /**
     * @return string|null reason code, or null when the key is usable:
     *         unconfigured | signing-material | malformed-descriptor |
     *         unsupported-format | wrong-network
     */
    public static function problem(): ?string
    {
        if (! self::isConfigured()) {
            return 'unconfigured';
        }

        try {
            $key = WalletKeyInput::parse(self::raw())['key'];
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }

        if (preg_match('/^' . self::networkPrefixes() . '[A-Za-z0-9]+$/', $key) === 1) {
            return null;
        }

        // An account key for the other network parses fine and derives fine —
        // it just derives addresses on a chain this deployment never watches.
        if (preg_match('/^[xyztuv]pub[A-Za-z0-9]+$/', $key) === 1) {
            return 'wrong-network';
        }

        return 'unsupported-format';
    }

    /**
     * @return array{key: string, script_type: 'bip84'|'bip86'|null}
     *
     * @throws InvalidArgumentException message is the reason code from problem()
     */
    public static function parsed(): array
    {
        $problem = self::problem();

        if ($problem !== null) {
            throw new InvalidArgumentException($problem);
        }

        return WalletKeyInput::parse(self::raw());
    }

    /**
     * Same allowlist onboarding applies to a pasted key.
     */
    private static function networkPrefixes(): string
    {
        return config('wallet.default_network') === 'mainnet' ? '(xpub|zpub)' : '(tpub|vpub)';
    }
}
