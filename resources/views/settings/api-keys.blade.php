<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col">
            <h2 class="mb-4 text-xl font-semibold leading-tight text-gray-800">
                Settings
            </h2>
            <div class="mt-8">
                @include('settings.partials.tabs')
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                <div class="p-6 space-y-6">
                    @if (session('status') === 'api-key-made')
                        <div class="rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800 space-y-2" style="border-color: currentColor;">
                            <p>Your new key. Copy it now; it will not be shown again.</p>
                            <code id="newApiKey" class="block select-all break-all rounded bg-white px-2 py-1 font-mono text-sm text-gray-900">{{ session('api_key_plain') }}</code>
                        </div>
                    @elseif (session('status') === 'api-key-revoked')
                        <div class="rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800" style="border-color: currentColor;">
                            Key revoked.
                        </div>
                    @endif

                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold text-gray-700">API keys</h3>
                        <p class="text-xs text-gray-600">
                            A key lets your own software create and read invoices for this account. See the <a href="https://github.com/n8bar/CryptoZing/blob/main/docs/API.md" class="underline">API reference</a>.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('settings.api-keys.store') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <x-input-label for="name" value="Key name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-64" :value="old('name')" required maxlength="100" placeholder="Shop website" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <x-primary-button>Make key</x-primary-button>
                    </form>

                    @if ($keys->isEmpty())
                        <p class="text-sm text-gray-600">No keys yet.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                    <th class="py-2">Name</th>
                                    <th class="py-2">Made</th>
                                    <th class="py-2">Last used</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($keys as $key)
                                    <tr class="border-t border-gray-200 {{ $key->revoked_at ? 'text-gray-400' : '' }}">
                                        <td class="py-2 pr-3">{{ $key->name }}</td>
                                        <td class="py-2 pr-3">{{ $key->created_at->format('D, Y-m-d H:i') }}</td>
                                        <td class="py-2 pr-3">{{ $key->last_used_at?->format('D, Y-m-d H:i') ?? 'Never used' }}</td>
                                        <td class="py-2 text-right">
                                            @if ($key->revoked_at)
                                                Revoked {{ $key->revoked_at->format('Y-m-d') }}
                                            @else
                                                <form method="POST" action="{{ route('settings.api-keys.revoke', $key) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-danger-button type="submit" class="!px-3 !py-1.5 !text-xs" aria-label="Revoke {{ $key->name }}">Revoke</x-danger-button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
