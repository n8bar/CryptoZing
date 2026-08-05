<?php

namespace App\Support;

class AlphaGate
{
    /**
     * Single read point for the alpha-gate flag so registration and login
     * can never disagree about whether the gate is on.
     */
    public static function enabled(): bool
    {
        return (bool) config('alpha.gate_enabled', true);
    }
}
