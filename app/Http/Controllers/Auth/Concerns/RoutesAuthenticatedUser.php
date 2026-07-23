<?php

namespace App\Http\Controllers\Auth\Concerns;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Shared post-login landing logic so a 2FA challenge completion lands the user
 * on the same destination a direct (non-2FA) login would: a stashed intended
 * URL wins, then support agents, then getting-started, then the dashboard.
 */
trait RoutesAuthenticatedUser
{
    protected function redirectAuthenticatedUser(Request $request, User $user): RedirectResponse
    {
        // A return-to-page target stashed by the 419 / session-expiry handler
        // wins over first-login persona routing — the user was actively
        // somewhere, so put them back there.
        if ($request->session()->has('url.intended')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($user->isSupportAgent()) {
            return redirect()->route('support.dashboard');
        }

        if ($user->gettingStartedNeedsAutoShow()) {
            return redirect()->route('getting-started.start');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
