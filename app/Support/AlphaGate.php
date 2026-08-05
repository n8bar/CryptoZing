<?php

namespace App\Support;

use App\Models\User;

class AlphaGate
{
    /**
     * Shared refusal copy so the password and second-factor halves of login
     * show the same message. Wrap in __() at the call site.
     */
    public const REFUSAL_MESSAGE = 'This account is awaiting approval — we\'ll email you when it\'s ready.';

    /**
     * Single read point for the alpha-gate flag so registration and login
     * can never disagree about whether the gate is on.
     */
    public static function enabled(): bool
    {
        return (bool) config('alpha.gate_enabled', true);
    }

    /**
     * Ban refusal copy — deliberately promise-free, unlike the approval
     * message. Wrap in __() at the call site.
     */
    public const BANNED_MESSAGE = 'This account has been disabled.';

    /**
     * Whether the gate refuses this account a session right now.
     */
    public static function blocks(User $user): bool
    {
        return self::enabled() && ! $user->isApproved();
    }

    /**
     * The refusal message that applies to this account, or null if it may
     * hold a session. Single decision point for login, the 2FA challenge,
     * and the per-request middleware. A ban wins over the alpha gate and
     * applies regardless of the gate flag.
     */
    public static function refusal(User $user): ?string
    {
        if ($user->isBanned()) {
            return __(self::BANNED_MESSAGE);
        }

        if (self::blocks($user)) {
            return __(self::REFUSAL_MESSAGE);
        }

        return null;
    }
}
