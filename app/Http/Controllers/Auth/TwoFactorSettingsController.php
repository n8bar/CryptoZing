<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactor\TwoFactorCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Settings-side management of the emailed second factor: begin enrollment
 * (send a code) and confirm it (activate). Login-time verification lives in
 * the guest-safe TwoFactorChallengeController.
 */
class TwoFactorSettingsController extends Controller
{
    public function __construct(private readonly TwoFactorCodeService $codes)
    {
    }

    /**
     * Begin email-2FA enrollment: email a verification code. Enrollment is a
     * round-trip — this does not enable 2FA, only stashes a pending code.
     */
    public function enroll(Request $request): RedirectResponse
    {
        $this->codes->sendCode($request->user());

        return back()->with('status', 'two-factor-code-sent');
    }

    /**
     * Confirm enrollment with the emailed code and enable email 2FA.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $this->codes->verifyCode($user, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('That code is invalid or has expired. Request a new one and try again.'),
            ]);
        }

        $user->forceFill(['two_factor_email_enabled_at' => now()])->save();

        return back()->with('status', 'two-factor-enabled');
    }
}
