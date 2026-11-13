<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Support\AlphaGate;
use App\Support\ApiError;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the issuer from a bearer API key and rate limits per key.
 * A key is refused while its owner may not sign in.
 */
class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();
        $key = $plain ? ApiKey::findByPlain($plain) : null;

        // A key works only while its owner may hold a session: not banned, and through the alpha gate.
        if (! $key || ! $key->user || AlphaGate::refusal($key->user) !== null) {
            return ApiError::response('unauthorized', 'Missing, unknown, or revoked API key.', 401);
        }

        $bucket = 'api-key:' . $key->id;
        $limit = config('api.rate_limit_per_minute');
        if (RateLimiter::tooManyAttempts($bucket, $limit)) {
            $retry = RateLimiter::availableIn($bucket);

            return ApiError::response('rate_limited', "Too many requests. Retry in {$retry} seconds.", 429, headers: ['Retry-After' => $retry]);
        }
        RateLimiter::hit($bucket, 60);

        $key->touchUsed();
        $request->setUserResolver(fn () => $key->user);
        $request->attributes->set('api_key', $key);

        return $next($request);
    }
}
