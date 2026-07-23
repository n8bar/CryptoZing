<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactor\TotpService;
use App\Services\TwoFactor\TwoFactorCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Settings-side management of the authenticator-app (TOTP) second factor:
 * generate a pending secret, show the enrollment screen, confirm it, and
 * disable it — each change gated by a valid code.
 */
class TwoFactorTotpController extends Controller
{
    public function __construct(
        private readonly TotpService $totp,
        private readonly TwoFactorCodeService $codes,
    ) {
    }

    /**
     * Begin setup: stash a pending secret (reused across reloads) and send the
     * user to the enrollment screen. A pending secret never gates login.
     */
    public function setup(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTotpEnabled()) {
            return redirect()->route('profile.edit');
        }

        if ($user->two_factor_totp_secret === null) {
            $user->forceFill([
                'two_factor_totp_secret' => $this->totp->generateSecret(),
                'two_factor_totp_confirmed_at' => null,
            ])->save();
        }

        return redirect()->route('two-factor.totp.setup.show');
    }

    /**
     * The enrollment screen: QR plus the manual-entry disclosure.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTotpEnabled() || $user->two_factor_totp_secret === null) {
            return redirect()->route('profile.edit');
        }

        $secret = $user->two_factor_totp_secret;

        return view('auth.two-factor-totp-setup', [
            'secret' => $secret,
            'otpauthUri' => $this->totp->otpauthUri($user, $secret),
        ]);
    }

    /**
     * Confirm the pending secret with a valid app code and enable TOTP.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if ($user->two_factor_totp_secret === null
            || ! $this->totp->verify($user->two_factor_totp_secret, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('That code did not match. Check your authenticator app and try again.'),
            ]);
        }

        $user->forceFill(['two_factor_totp_confirmed_at' => now()])->save();

        return redirect()->route('profile.edit')->with('status', 'totp-enabled');
    }

    /**
     * Disable TOTP after re-verifying with a current app code (or the emailed
     * fallback code).
     */
    public function disable(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        $verified = $user->two_factor_totp_secret !== null
            && $this->totp->verify($user->two_factor_totp_secret, $validated['code']);

        if (! $verified) {
            $verified = $this->codes->verifyCode($user, $validated['code']);
        }

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => __('That code is invalid or has expired. Try again.'),
            ]);
        }

        $user->forceFill([
            'two_factor_totp_secret' => null,
            'two_factor_totp_confirmed_at' => null,
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'totp-disabled');
    }
}
