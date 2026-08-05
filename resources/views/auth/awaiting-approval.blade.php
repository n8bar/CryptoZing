<x-guest-layout>
    <div class="mb-4 text-sm auth-subtext">
        {{ __('Your account has been created. CryptoZing is in an invite-only alpha, so access is granted manually — we\'ll email you at the address you registered once your account is approved.') }}
    </div>

    <div class="mt-4">
        <a href="{{ route('login') }}" class="underline text-sm auth-link font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:ring-offset-0">
            {{ __('Back to login') }}
        </a>
    </div>
</x-guest-layout>
