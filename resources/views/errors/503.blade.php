<x-error-layout code="503" title="Down for maintenance">
    CryptoZing is briefly offline for scheduled maintenance. Please check back in a few minutes.

    {{-- Maintenance mode may run before auth boots, so keep recovery auth-free. --}}
    <x-slot:actions>
        <a class="cz-btn cz-btn--primary" href="/">Try again</a>
    </x-slot:actions>
</x-error-layout>
