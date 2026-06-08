<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Long-lived marker dropped at login so a later guest redirect can tell an
     * expired session ("you were signed in") apart from a never-authenticated
     * visit, and surface the expired-session banner only in the former case.
     */
    public const RETURNING_COOKIE = 'cz_returning';

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Outlives the session so a later expiry can be recognised as one.
        Cookie::queue(Cookie::forever(self::RETURNING_COOKIE, '1'));

        // A return-to-page target stashed by the 419 / session-expiry handler
        // wins over first-login persona routing — the user was actively
        // somewhere, so put them back there.
        if ($request->session()->has('url.intended')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $user = $request->user();
        if ($user && $user->isSupportAgent()) {
            return redirect()->route('support.dashboard');
        }

        if ($user && $user->gettingStartedNeedsAutoShow()) {
            return redirect()->route('getting-started.start');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Deliberate logout, not an expiry — drop the marker so the next guest
        // redirect lands on a plain /login without the expired-session banner.
        Cookie::queue(Cookie::forget(self::RETURNING_COOKIE));

        return redirect('/');
    }
}
