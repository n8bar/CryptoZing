<section class="space-y-8">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add a second step at sign-in. Use an authenticator app, emailed codes, or both.') }}
        </p>
    </header>

    {{-- Authenticator app (TOTP) --}}
    <div class="space-y-4">
        <h3 class="text-sm font-semibold text-gray-900">{{ __('Authenticator app') }}</h3>

        @if ($user->hasTotpEnabled())
            <p class="text-sm text-gray-600">
                {{ __('On. Your authenticator app is asked for a code at sign-in; email codes are available as a backup.') }}
            </p>

            <form method="post" action="{{ route('two-factor.totp.disable') }}" class="space-y-4">
                @csrf
                @method('delete')

                <div>
                    <x-input-label for="totp_disable_code" :value="__('Enter a current app code to turn it off')" />
                    <x-text-input
                        id="totp_disable_code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        class="mt-1 block w-full tracking-widest"
                    />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <x-danger-button>{{ __('Disable authenticator app') }}</x-danger-button>
            </form>
        @elseif ($user->hasPendingTotpEnrollment())
            <p class="text-sm text-gray-600">
                {{ __('Setup started but not finished.') }}
            </p>
            <a href="{{ route('two-factor.totp.setup.show') }}" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Finish authenticator setup') }}
            </a>
        @else
            <p class="text-sm text-gray-600">
                {{ __('Use a time-based code from an app like Google Authenticator or Authy.') }}
            </p>
            <form method="post" action="{{ route('two-factor.totp.setup') }}">
                @csrf
                <x-primary-button>{{ __('Set up authenticator app') }}</x-primary-button>
            </form>
        @endif
    </div>

    {{-- Email codes --}}
    <div class="space-y-4 border-t border-gray-100 pt-6">
        <h3 class="text-sm font-semibold text-gray-900">{{ __('Email codes') }}</h3>

        @if ($user->hasEmailTwoFactorEnabled())
            @if ($user->two_factor_code_hash !== null)
                <form method="post" action="{{ route('two-factor.email.disable') }}" class="space-y-4">
                    @csrf
                    @method('delete')

                    <p class="text-sm text-gray-600">
                        {{ __('We emailed a 6-digit code. Enter it below to turn off email codes.') }}
                    </p>

                    <div>
                        <x-input-label for="two_factor_disable_code" :value="__('Verification code')" />
                        <x-text-input
                            id="two_factor_disable_code"
                            name="code"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            class="mt-1 block w-full tracking-widest"
                            autofocus
                        />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <x-danger-button>{{ __('Confirm and disable') }}</x-danger-button>
                </form>

                <form method="post" action="{{ route('two-factor.email.disable-request') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
                        {{ __('Resend code') }}
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-600">
                    {{ __('On. Email two-factor authentication is on. Turning it off needs a fresh emailed code.') }}
                </p>
                <form method="post" action="{{ route('two-factor.email.disable-request') }}">
                    @csrf
                    <x-danger-button>{{ __('Disable two-factor') }}</x-danger-button>
                </form>
            @endif
        @elseif ($user->hasPendingEmailTwoFactorEnrollment())
            <form method="post" action="{{ route('two-factor.email.confirm') }}" class="space-y-4">
                @csrf

                <p class="text-sm text-gray-600">
                    {{ __('We emailed a 6-digit code. Enter it below to finish turning on email codes.') }}
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
                    />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Confirm and enable') }}</x-primary-button>
            </form>

            <form method="post" action="{{ route('two-factor.email.enroll') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
                    {{ __('Resend code') }}
                </button>
            </form>
        @else
            <p class="text-sm text-gray-600">
                {{ __('We email a 6-digit code you enter right after your password.') }}
            </p>
            <form method="post" action="{{ route('two-factor.email.enroll') }}">
                @csrf
                <x-primary-button>{{ __('Enable email two-factor') }}</x-primary-button>
            </form>
        @endif
    </div>
</section>
