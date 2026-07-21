<x-public-layout title="Donate">
    @push('head')
        <style>
            .donate-receipt-rule { border: none; border-top: 2px dashed rgb(148 163 184 / 0.45); }
            @media print {
                nav, header, footer, .donate-no-print { display: none !important; }
                body { background: #fff !important; }
                .donate-receipt { box-shadow: none !important; border: 1px solid #000 !important; color: #000 !important; background: #fff !important; }
                .donate-receipt * { color: #000 !important; }
            }
        </style>
    @endpush

    <div class="py-10">
        <div class="mx-auto max-w-xl px-4 sm:px-6">
            @if ($donation && $donation->status === 'paid')
                {{-- Signature artifact: the donation record, set like a till receipt --}}
                <div class="donate-receipt bg-white dark:bg-slate-800 shadow rounded-lg p-6 font-mono text-sm text-gray-900 dark:text-slate-100">
                    <div class="text-center">
                        <p class="text-lg font-semibold tracking-widest uppercase">CryptoZing</p>
                        <p class="mt-1 text-xs tracking-widest uppercase text-gray-600 dark:text-slate-400">Donation record</p>
                    </div>

                    <hr class="donate-receipt-rule my-4">

                    <p class="font-sans text-base">Thank you for supporting CryptoZing!</p>
                    <p class="mt-2 font-sans text-sm text-gray-600 dark:text-slate-400" aria-live="polite">
                        Your donation was seen on the network.
                        <strong class="text-gray-900 dark:text-slate-100">Save or print this receipt now</strong> — it's only available in this browser session.
                    </p>

                    <hr class="donate-receipt-rule my-4">

                    <dl class="space-y-1.5">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-600 dark:text-slate-400">Seen at</dt>
                            <dd class="text-right">{{ optional($donation->paid_at)->format('Y-m-d H:i T') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-600 dark:text-slate-400">Requested</dt>
                            <dd class="text-right">${{ number_format((float) $donation->usd_amount_requested, 2) }} USD</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-600 dark:text-slate-400">Received</dt>
                            <dd class="text-right">{{ number_format((int) $donation->sats_received) }} sats</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-600 dark:text-slate-400"><span class="sr-only">Received (BTC)</span></dt>
                            <dd class="text-right">{{ rtrim(rtrim(number_format($donation->sats_received / 100000000, 8, '.', ''), '0'), '.') }} BTC</dd>
                        </div>
                    </dl>

                    <hr class="donate-receipt-rule my-4">

                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-600 dark:text-slate-400">Address</p>
                            <p class="break-all">{{ $donation->address }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-slate-400">Transaction</p>
                            <p class="break-all">{{ $donation->txid }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-slate-400">Network</p>
                            <p>{{ $donation->network }}</p>
                        </div>
                    </div>

                    <hr class="donate-receipt-rule my-4">

                    <p class="text-xs text-gray-600 dark:text-slate-400">
                        Donations support CryptoZing LLC. They are non-refundable and not tax-deductible. This is a payment record, not a tax document.
                    </p>

                    <div class="donate-no-print mt-6 flex flex-wrap items-center gap-3">
                        <x-primary-button type="button" onclick="window.print()">Print receipt</x-primary-button>
                        <form method="POST" action="{{ route('donate.reset') }}">
                            @csrf
                            <x-secondary-button type="submit">Donate again</x-secondary-button>
                        </form>
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                        <span class="font-sans text-sm text-gray-600 dark:text-slate-400">Payment seen</span>
                    </div>
                </div>
            @elseif ($donation && $donation->status === 'pending')
                <div class="bg-white dark:bg-slate-800 shadow rounded-lg p-6">
                    <h1 class="text-2xl font-semibold mb-1">Send your donation</h1>
                    <p class="mb-5 text-sm text-gray-600 dark:text-slate-400">Scan the code or copy the address. This page updates on its own once your payment is seen.</p>
                    <noscript>
                        <p class="mb-5 text-sm text-gray-600 dark:text-slate-400">JavaScript is off in your browser — refresh this page after sending; your receipt appears once the payment is seen.</p>
                    </noscript>

                    @if ($rateUnavailable)
                        <p class="mb-5 rounded-md border border-amber-300 bg-amber-50 dark:bg-amber-950/40 dark:border-amber-700 px-3 py-2 text-sm">
                            Live rate unavailable — your chosen amount can't be converted to BTC right now. You can still send any amount to this address.
                        </p>
                    @endif

                    @if ($bitcoinUri)
                        <div class="donate-no-print mb-5 flex justify-center" aria-hidden="true">
                            <div class="rounded-lg p-3 shadow-sm border border-gray-200 dark:border-slate-500" style="background:#ffffff">
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->margin(1)->generate($bitcoinUri) !!}
                            </div>
                        </div>
                    @endif

                    <div class="donate-no-print mb-5 flex items-center gap-2">
                        <span class="relative inline-flex h-2.5 w-2.5" aria-hidden="true">
                            <span class="absolute inline-flex h-full w-full animate-ping motion-reduce:animate-none rounded-full bg-amber-400 opacity-60"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        </span>
                        <span class="text-sm text-gray-600 dark:text-slate-400">Watching for payment…</span>
                    </div>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="font-medium">Address</dt>
                            <dd class="mt-0.5 font-mono break-all" id="donation-address">{{ $donation->address }}</dd>
                        </div>
                        @if ($btcAmount)
                            <div>
                                <dt class="font-medium">Amount</dt>
                                <dd class="mt-0.5">{{ $btcAmount }} BTC <span class="text-gray-600 dark:text-slate-400">(${{ number_format((float) $donation->usd_amount_requested, 2) }} USD at the current rate)</span></dd>
                            </div>
                        @endif
                        @if ($bitcoinUri)
                            <div>
                                <dt class="font-medium">Payment URI</dt>
                                <dd class="mt-0.5 font-mono break-all text-gray-600 dark:text-slate-400">{{ $bitcoinUri }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="donate-no-print mt-4">
                        <x-secondary-button type="button" id="donation-copy">Copy address</x-secondary-button>
                    </div>

                    <details class="donate-no-print mt-6" @if ($errors->has('amount')) open @endif>
                        <summary class="cursor-pointer text-sm text-gray-600 dark:text-slate-400">Change amount</summary>
                        <form method="POST" action="{{ route('donate.allocate') }}" class="mt-3 flex items-end gap-2">
                            @csrf
                            <div>
                                <x-input-label for="amount" value="New amount (USD)" />
                                <x-text-input type="number" name="amount" id="amount" min="1" max="25000" step="0.01"
                                    :value="old('amount', $donation->usd_amount_requested)" class="mt-1 block w-40"
                                    @if ($errors->has('amount')) autofocus @endif />
                            </div>
                            <x-primary-button>Update</x-primary-button>
                        </form>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </details>

                    <p class="mt-6 text-xs text-gray-600 dark:text-slate-400">
                        Donations support CryptoZing LLC. They are non-refundable and not tax-deductible.
                    </p>
                </div>

                <script>
                    (function () {
                        const poll = setInterval(function () {
                            fetch('{{ route('donate.status') }}', { headers: { 'Accept': 'application/json' } })
                                .then(function (response) { return response.json(); })
                                .then(function (data) {
                                    if (data.paid) {
                                        clearInterval(poll);
                                        window.location.reload();
                                    }
                                })
                                .catch(function () {});
                        }, 10000);

                        const copyButton = document.getElementById('donation-copy');
                        if (copyButton) {
                            copyButton.addEventListener('click', function () {
                                const text = document.getElementById('donation-address').textContent.trim();
                                const finish = function (ok) {
                                    copyButton.textContent = ok ? 'Copied!' : 'Copy failed';
                                    setTimeout(function () { copyButton.textContent = 'Copy address'; }, 1200);
                                };
                                if (window.isSecureContext && navigator.clipboard) {
                                    navigator.clipboard.writeText(text).then(function () { finish(true); }, function () { finish(false); });
                                } else {
                                    const helper = document.createElement('textarea');
                                    helper.value = text;
                                    document.body.appendChild(helper);
                                    helper.select();
                                    let ok = false;
                                    try { ok = document.execCommand('copy'); } catch (e) {}
                                    helper.remove();
                                    finish(ok);
                                }
                            });
                        }
                    })();
                </script>
            @else
                <div class="bg-white dark:bg-slate-800 shadow rounded-lg p-6">
                    <h1 class="text-2xl font-semibold mb-1">Support CryptoZing</h1>
                    <p class="mb-5 text-sm text-gray-600 dark:text-slate-400">CryptoZing is free while in open beta. If it saves you time, a Bitcoin donation helps keep it running.</p>

                    @if ($poolBusy)
                        <p class="mb-5 rounded-md border border-amber-300 bg-amber-50 dark:bg-amber-950/40 dark:border-amber-700 px-3 py-2 text-sm">
                            The donation queue is full right now — please try again in a little while.
                        </p>
                    @endif

                    <form method="POST" action="{{ route('donate.allocate') }}">
                        @csrf
                        <fieldset class="mb-5">
                            <legend class="text-sm font-medium mb-2">Choose an amount</legend>
                            <div class="flex flex-wrap gap-2">
                                <x-secondary-button type="submit" name="preset_amount" value="5">Donate $5</x-secondary-button>
                                <x-secondary-button type="submit" name="preset_amount" value="25">Donate $25</x-secondary-button>
                                <x-secondary-button type="submit" name="preset_amount" value="100">Donate $100</x-secondary-button>
                            </div>
                        </fieldset>

                        <div class="flex items-end gap-2">
                            <div>
                                <x-input-label for="amount" value="Custom amount (USD)" />
                                <x-text-input type="number" name="amount" id="amount" min="1" max="25000" step="0.01"
                                    :value="old('amount')" class="mt-1 block w-40"
                                    @if ($errors->has('amount')) autofocus @endif />
                            </div>
                            <x-primary-button>Donate</x-primary-button>
                        </div>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </form>

                    <p class="mt-6 text-xs text-gray-600 dark:text-slate-400">
                        Donations support CryptoZing LLC. They are non-refundable and not tax-deductible.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
