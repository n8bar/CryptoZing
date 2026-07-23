@if (! auth()->user()->hasAnyTwoFactorEnabled() && ! session(\App\Http\Controllers\TwoFactorRecommendationController::SESSION_KEY))
    <div class="flex items-start justify-between gap-4 rounded-xl border border-indigo-200 bg-indigo-50 p-4 shadow-sm dark:border-indigo-400/25 dark:bg-indigo-950/35"
         style="border-color: currentColor;"
         role="region"
         aria-label="Security recommendation">
        <div class="text-sm text-indigo-900 dark:text-indigo-100">
            <p class="font-semibold">{{ __('Add two-factor authentication') }}</p>
            <p class="mt-1 text-indigo-800 dark:text-indigo-200/90">
                {{ __('Protect your account with a second step at sign-in — an authenticator app or emailed codes.') }}
            </p>
            <a href="{{ route('profile.edit') }}#two-factor"
               class="mt-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Enable 2FA') }}
            </a>
        </div>

        <form method="POST" action="{{ route('two-factor.recommendation.dismiss') }}">
            @csrf
            <button type="submit"
                    aria-label="{{ __('Dismiss') }}"
                    class="rounded p-1 text-indigo-500 transition hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-indigo-300 dark:hover:text-indigo-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
            </button>
        </form>
    </div>
@endif
