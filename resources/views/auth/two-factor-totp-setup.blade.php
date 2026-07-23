<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Set up authenticator app') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl space-y-6">
                    <ol class="list-decimal list-inside text-sm text-gray-600 space-y-1">
                        <li>{{ __('Open your authenticator app (Google Authenticator, Authy, 1Password, …).') }}</li>
                        <li>{{ __('Scan this QR code, then enter the 6-digit code it shows below.') }}</li>
                    </ol>

                    <div class="flex justify-center rounded-lg border border-gray-200 bg-white p-4">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(1)->generate($otpauthUri) !!}
                    </div>

                    <details class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                        <summary class="cursor-pointer font-medium text-gray-900">
                            {{ __("Can't scan? Set up manually") }}
                        </summary>
                        <div class="mt-3 space-y-3">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Setup key (base32)') }}</div>
                                <code class="mt-1 block break-all rounded bg-white px-2 py-1 font-mono text-sm text-gray-900 select-all">{{ $secret }}</code>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('otpauth URI') }}</div>
                                <code class="mt-1 block break-all rounded bg-white px-2 py-1 font-mono text-sm text-gray-900 select-all">{{ $otpauthUri }}</code>
                            </div>
                            <p class="text-xs text-gray-500">{{ __('Account is your email; the code is time-based (TOTP).') }}</p>
                        </div>
                    </details>

                    <form method="post" action="{{ route('two-factor.totp.confirm') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="code" :value="__('Verification code')" />
                            <x-text-input
                                id="code"
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
                            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
