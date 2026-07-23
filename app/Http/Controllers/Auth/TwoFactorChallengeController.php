<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactor\TwoFactorCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The guest-safe second-factor gate. A user who passed the password check is
 * held here (logged out, pending id stashed in the session) until they present
 * a valid code; only then does the session guard actually log them in.
 */
class TwoFactorChallengeController extends Controller
{
    /** Session key holding the half-authenticated login. */
    public const SESSION_KEY = 'two_factor.login';

    public function __construct(private readonly TwoFactorCodeService $codes)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', [
            'method' => $pending['method'] ?? 'email',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = User::find($pending['id']);

        if (! $user) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('login');
        }

        if ($this->codes->isLocked($user)) {
            throw ValidationException::withMessages([
                'code' => __('Too many attempts. Please wait a few minutes before trying again.'),
            ]);
        }

        if (! $this->codes->verifyCode($user, $validated['code'])) {
            $this->codes->recordFailedAttempt($user);

            throw ValidationException::withMessages([
                'code' => __('That code is invalid or has expired. Request a new one and try again.'),
            ]);
        }

        // Second factor satisfied — complete the login with the same fixation
        // protection as a password login.
        Auth::guard('web')->login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();

        Cookie::queue(Cookie::forever(AuthenticatedSessionController::RETURNING_COOKIE, '1'));

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Resend an emailed code (also the TOTP email-fallback trigger), subject to
     * the per-user send cap.
     */
    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('login');
        }

        $user = User::find($pending['id']);

        if (! $user) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('login');
        }

        if ($this->codes->tooManyRecentSends($user)) {
            return back()->withErrors([
                'code' => __('Too many code requests. Please wait a few minutes before trying again.'),
            ]);
        }

        $this->codes->sendCode($user);

        return back()->with('status', __('A new code is on its way.'));
    }
}
