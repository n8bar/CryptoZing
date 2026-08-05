<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Mail\AccountApprovedMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportApprovalController extends Controller
{
    /**
     * Clear an account through the alpha gate and tell them by mail.
     */
    public function approve(User $user): RedirectResponse
    {
        if (! $user->isApproved()) {
            $user->forceFill(['approved_at' => now()])->save();

            Mail::to($user->email)->queue(new AccountApprovedMail($user));
        }

        return back()->with('status', __(':email approved.', ['email' => $user->email]));
    }

    /**
     * Pull an account back behind the gate. No mail — revocation is an
     * operator action, not a notification event.
     */
    public function revoke(Request $request, User $user): RedirectResponse
    {
        // Self-revoke would lock the operator out of the only surface that
        // can undo it.
        if ($request->user()->is($user)) {
            return back()->withErrors(['revoke' => __('You can\'t revoke your own account.')]);
        }

        $user->forceFill(['approved_at' => null])->save();

        return back()->with('status', __(':email revoked.', ['email' => $user->email]));
    }
}
