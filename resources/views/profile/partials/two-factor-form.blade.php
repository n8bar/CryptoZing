<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add a second step at sign-in. We email a 6-digit code you enter right after your password.') }}
        </p>
    </header>

    @if ($user->hasEmailTwoFactorEnabled())
        <div class="mt-6 space-y-2">
            <p class="text-sm font-medium text-gray-900">
                {{ __('Email two-factor authentication is on.') }}
            </p>
            <p class="text-sm text-gray-600">
                {{ __('You will be asked for an emailed code each time you sign in.') }}
            </p>
        </div>
    @elseif ($user->hasPendingEmailTwoFactorEnrollment())
        <form method="post" action="{{ route('two-factor.email.confirm') }}" class="mt-6 space-y-6">
            @csrf

            <p class="text-sm text-gray-600">
                {{ __('We emailed a 6-digit code. Enter it below to finish turning on two-factor authentication.') }}
            </p>

            <div>
                <x-input-label for="two_factor_code" :value="__('Verification code')" />
                <x-text-input
                    id="two_factor_code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    class="mt-1 block w-full tracking-widest"
                    autofocus
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Confirm and enable') }}</x-primary-button>
            </div>
        </form>

        <form method="post" action="{{ route('two-factor.email.enroll') }}" class="mt-3">
            @csrf
            <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ __('Resend code') }}
            </button>
        </form>
    @else
        <form method="post" action="{{ route('two-factor.email.enroll') }}" class="mt-6">
            @csrf
            <x-primary-button>{{ __('Enable email two-factor') }}</x-primary-button>
        </form>
    @endif
</section>
