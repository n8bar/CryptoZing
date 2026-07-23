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
    /**
     * Session flags recording that the user explicitly started an enroll or a
     * disable from settings. The card keys its code-entry prompts on these, not
     * on the shared code column — a code minted by another flow (login,
     * wallet step-up) must never surface an enable/disable form here.
     */
    public const ENROLL_PENDING_KEY = 'two_factor.email_enroll_pending';

    public const DISABLE_PENDING_KEY = 'two_factor.email_disable_pending';

    public function __construct(private readonly TwoFactorCodeService $codes)
    {
    }

    /**
     * Begin email-2FA enrollment: email a verification code. Enrollment is a
     * round-trip — this does not enable 2FA, only stashes a pending code.
     */
    public function enroll(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($this->codes->tooManyRecentSends($user)) {
            return back()->withErrors([
                'code' => __('Too many code requests. Please wait a few minutes before trying again.'),
            ]);
        }

        $this->codes->sendCode($user);
        $request->session()->put(self::ENROLL_PENDING_KEY, true);

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
        $request->session()->forget(self::ENROLL_PENDING_KEY);

        return back()->with('status', 'two-factor-enabled');
    }

    /**
     * Email a re-verification code to begin disabling email 2FA.
     */
    public function requestDisable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasEmailTwoFactorEnabled()) {
            return back();
        }

        if ($this->codes->tooManyRecentSends($user)) {
            return back()->withErrors([
                'code' => __('Too many code requests. Please wait a few minutes before trying again.'),
            ]);
        }

        $this->codes->sendCode($user);
        $request->session()->put(self::DISABLE_PENDING_KEY, true);

        return back()->with('status', 'two-factor-disable-code-sent');
    }

    /**
     * Disable email 2FA after re-verifying with a current valid code.
     */
    public function disable(Request $request): RedirectResponse
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

        $user->forceFill([
            'two_factor_email_enabled_at' => null,
            'two_factor_code_hash' => null,
            'two_factor_code_expires_at' => null,
            'two_factor_attempts' => 0,
            'two_factor_locked_until' => null,
        ])->save();
        $request->session()->forget(self::DISABLE_PENDING_KEY);

        return back()->with('status', 'two-factor-disabled');
    }
}
