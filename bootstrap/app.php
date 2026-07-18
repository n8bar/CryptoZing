<?php

use App\Console\Commands\AssignInvoiceAddresses;
use App\Console\Commands\BackfillInvoicePayments;
use App\Console\Commands\ReassignInvoiceAddresses;
use App\Console\Commands\SendPastDueInvoiceAlerts;
use App\Console\Commands\WatchInvoicePayments;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withEvents(discover: false)
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('wallet:watch-payments')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
        $schedule->command('invoices:send-past-due-alerts')->dailyAt('02:00');
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        AssignInvoiceAddresses::class,
        BackfillInvoicePayments::class,
        SendPastDueInvoiceAlerts::class,
        WatchInvoicePayments::class,
        ReassignInvoiceAddresses::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/mailgun',
        ]);

        // Non-sensitive boolean marker — left unencrypted so it reads as plain
        // text in the guest-redirect check below (and in tests).
        $middleware->encryptCookies(except: [
            \App\Http\Controllers\Auth\AuthenticatedSessionController::RETURNING_COOKIE,
        ]);

        // Guests bounced from a protected page get the expired-session banner
        // only when they carry the returning-user marker (their session expired),
        // not on a first, never-authenticated visit.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->hasCookie(\App\Http\Controllers\Auth\AuthenticatedSessionController::RETURNING_COOKIE)
                ? route('login', ['expired' => 1])
                : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $renderForbidden = function (Request $request, ?string $details = null) {
            $fallback = "Sorry, you don't have permission.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $details ?: $fallback,
                ], 403);
            }

            return response()->view('errors.403', [
                'details' => $details,
            ], 403);
        };

        $renderLogoutRedirect = function (Request $request) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->noContent();
            }

            return redirect('/');
        };

        $renderSessionExpired = function (Request $request) {
            $returnTo = \App\Support\ReturnToResolver::resolve($request);

            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($returnTo !== null) {
                    $request->session()->put('url.intended', $returnTo);
                }
            }

            $loginUrl = route('login', ['expired' => 1]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please sign in again.',
                    'redirect' => $loginUrl,
                ], 419);
            }

            return redirect()->to($loginUrl);
        };

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($renderForbidden) {
            return $renderForbidden($request, $exception->getMessage());
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($renderForbidden, $renderLogoutRedirect, $renderSessionExpired) {
            if ($exception->getStatusCode() === 419) {
                if ($request->routeIs('logout') || $request->is('logout')) {
                    return $renderLogoutRedirect($request);
                }

                return $renderSessionExpired($request);
            }

            if ($exception->getStatusCode() !== 403) {
                return null;
            }

            return $renderForbidden($request, $exception->getMessage());
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) use ($renderLogoutRedirect, $renderSessionExpired) {
            if ($request->routeIs('logout') || $request->is('logout')) {
                return $renderLogoutRedirect($request);
            }

            return $renderSessionExpired($request);
        });
    })->create();
