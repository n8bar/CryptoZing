<?php

namespace App\Http\Middleware;

use App\Support\AlphaGate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Drops a session whose account lost alpha approval, so a revoke takes effect
 * on the account's next request instead of at its next login. Works under any
 * session driver — the check rides the already-loaded user, no extra queries.
 */
class EnsureApprovedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user && AlphaGate::blocks($user)) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => __(AlphaGate::REFUSAL_MESSAGE)]);
        }

        return $next($request);
    }
}
