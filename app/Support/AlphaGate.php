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
     * Whether the gate refuses this account a session right now.
     */
    public static function blocks(User $user): bool
    {
        return self::enabled() && ! $user->isApproved();
    }
}
