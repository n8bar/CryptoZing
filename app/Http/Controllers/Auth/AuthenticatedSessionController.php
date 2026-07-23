<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RoutesAuthenticatedUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\TwoFactor\TwoFactorCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use RoutesAuthenticatedUser;

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
    public function store(LoginRequest $request, TwoFactorCodeService $codes): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // 2FA users are held at the challenge: undo the credential login and
        // stash a pending id; the challenge controller re-logs in only after a
        // valid second factor. The intended-URL target (if any) survives in the
        // session and is replayed on success.
        if ($user && $user->requiresTwoFactorChallenge()) {
            $remember = $request->boolean('remember');

            Auth::guard('web')->logout();

            $request->session()->put(TwoFactorChallengeController::SESSION_KEY, [
                'id' => $user->id,
                'remember' => $remember,
                'method' => $user->twoFactorLoginMethod(),
            ]);

            // Email-led challenge: send the first code as login diverts — unless
            // the send cap is already reached (a valid code still exists in that
            // window), so repeated login POSTs can't flood the inbox.
            if ($user->twoFactorLoginMethod() === 'email' && ! $codes->tooManyRecentSends($user)) {
                $codes->sendCode($user);
            }

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        // Outlives the session so a later expiry can be recognised as one.
        Cookie::queue(Cookie::forever(self::RETURNING_COOKIE, '1'));

        return $this->redirectAuthenticatedUser($request, $user);
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
