<x-guest-layout>
    <div class="grid gap-10 lg:grid-cols-2 items-center">
        <div class="space-y-4 max-w-xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] auth-muted">One more step</p>
            <h1 class="text-4xl font-semibold auth-heading leading-tight">
                Two-factor verification
            </h1>
            <p class="text-base auth-muted">
                @if ($method === 'email')
                    We emailed a 6-digit code to your address. Enter it below to finish signing in.
                @else
                    Enter the 6-digit code from your authenticator app to finish signing in.
                @endif
            </p>
        </div>

        <div class="auth-card shadow-indigo-900/30 backdrop-blur">
            @if ($errors->any())
                <div class="mb-4 rounded-lg alert-error px-4 py-3 text-sm">
                    <div class="font-semibold">We couldn’t verify that code.</div>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <label for="code" class="block text-sm font-semibold auth-heading">Verification code</label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                        autofocus
                        class="auth-input w-full tracking-[0.4em] text-center text-lg placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400 focus:ring-offset-0"
                        placeholder="000000"
                    >
                </div>

                <div class="pt-2 space-y-3">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold shadow-lg shadow-indigo-900/40 transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:ring-offset-0 btn-primary bg-indigo-600 text-white dark:bg-indigo-500">
                        Verify and continue
                    </button>
                </div>
            </form>

            @if ($method === 'email' && Route::has('two-factor.challenge.resend'))
                <form method="POST" action="{{ route('two-factor.challenge.resend') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" class="text-sm font-semibold auth-link">
                        Resend code
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-guest-layout>
