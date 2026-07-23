<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Dismissal of the non-blocking "enable 2FA" dashboard banner. Dismissal is a
 * session-only flag — no schema, no persistence — so the banner reappears next
 * session if 2FA is still off.
 */
class TwoFactorRecommendationController extends Controller
{
    public const SESSION_KEY = 'two_factor_banner_dismissed';

    public function dismiss(Request $request): RedirectResponse
    {
        $request->session()->put(self::SESSION_KEY, true);

        return back();
    }
}
