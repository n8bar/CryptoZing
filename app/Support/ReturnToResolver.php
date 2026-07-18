<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Resolves the page a user should be returned to after re-authenticating
 * following a 419 / session-expiry bounce.
 *
 * The candidate is read from a hidden `_return_to` field first (it rides in
 * the POST body, so it survives even when the session and CSRF token are
 * gone), then falls back to the Referer header. A candidate is only accepted
 * when it points at a same-origin GET route — never the POST/PATCH/DELETE
 * action that just failed, so the mutation is never implicitly replayed.
 * Anything that fails validation returns null, and callers fall back to the
 * dashboard.
 */
class ReturnToResolver
{
    public static function resolve(Request $request): ?string
    {
        $candidate = $request->input('_return_to');

        if (! is_string($candidate) || trim($candidate) === '') {
            $candidate = $request->headers->get('referer');
        }

        if (! is_string($candidate) || trim($candidate) === '') {
            return null;
        }

        $candidate = trim($candidate);

        if (! self::isSameOrigin($request, $candidate)) {
            return null;
        }

        if (! self::resolvesToGetRoute($candidate)) {
            return null;
        }

        return $candidate;
    }

    private static function isSameOrigin(Request $request, string $candidate): bool
    {
        $parts = parse_url($candidate);

        if ($parts === false || ! isset($parts['host'])) {
            return false;
        }

        if (($parts['scheme'] ?? $request->getScheme()) !== $request->getScheme()) {
            return false;
        }

        return strcasecmp($parts['host'], $request->getHost()) === 0;
    }

    private static function resolvesToGetRoute(string $candidate): bool
    {
        $path = parse_url($candidate, PHP_URL_PATH) ?: '/';

        $probe = Request::create($path, 'GET');

        try {
            $route = Route::getRoutes()->match($probe);
        } catch (HttpException | UrlGenerationException) {
            return false;
        }

        return in_array('GET', $route->methods(), true);
    }
}
