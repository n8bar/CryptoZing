<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight">Support Dashboard</h2>
                <p class="text-sm text-gray-500">Read-only access to issuers who have active support grants.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-green-700">{{ session('status') }}</div>
            @endif

            @if ($errors->has('revoke') || $errors->has('ban'))
                <div class="rounded-md bg-red-50 p-3 text-red-700">{{ $errors->first('revoke') ?: $errors->first('ban') }}</div>
            @endif

            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-400/40 dark:bg-blue-950/30 dark:text-blue-100">
                Issuer data on this surface is read-only. Support access depends on an active issuer grant and expires automatically.
            </div>

            {{-- Alpha gate: pending account approvals --}}
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-white/10 dark:bg-slate-900/80">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">Pending Approvals</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-slate-900/90">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Registered</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-slate-900/80">
                            @forelse ($pendingApprovals as $account)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $account->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">{{ $account->email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">{{ $account->created_at?->setTimezone(config('app.timezone'))->toDayDateTimeString() }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <form method="POST" action="{{ route('support.approvals.approve', $account) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-indigo-500">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-slate-400">No accounts awaiting approval.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($pendingApprovals->hasPages())
                    <div class="px-6 py-4">{{ $pendingApprovals->onEachSide(1)->links() }}</div>
                @endif
            </div>

            {{-- Account lookup: ban/unban/revoke surface for any account --}}
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-white/10 dark:bg-slate-900/80">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">Account Lookup</h3>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <form method="GET" action="{{ route('support.dashboard') }}" class="flex flex-wrap items-center gap-2">
                        <label for="account" class="sr-only">Account email</label>
                        <input id="account" name="account" type="text" value="{{ $lookupEmail }}" placeholder="account email (partial ok)"
                            class="w-72 max-w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-white/20 dark:bg-slate-900 dark:text-white">
                        <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-slate-100 dark:hover:bg-white/10">Look up</button>
                    </form>

                    @if ($lookupEmail !== '' && $lookupAccounts->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-slate-400">No account matching that email.</p>
                    @endif

                    @if ($lookupOverflow)
                        <p class="text-sm text-amber-600 dark:text-amber-400">More than 10 matches — narrow the search.</p>
                    @endif

                    @foreach ($lookupAccounts as $lookupAccount)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-gray-200 px-4 py-3 dark:border-white/10">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $lookupAccount->name }}</p>
                                <p class="text-sm text-gray-700 dark:text-slate-300">{{ $lookupAccount->email }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    Registered {{ $lookupAccount->created_at?->setTimezone(config('app.timezone'))->toDayDateTimeString() }}
                                    —
                                    @if ($lookupAccount->isBanned())
                                        <span class="font-semibold text-red-600 dark:text-red-400">Banned</span>
                                    @elseif ($lookupAccount->isApproved())
                                        <span class="font-semibold text-green-600 dark:text-green-400">Approved</span>
                                    @else
                                        <span class="font-semibold text-amber-600 dark:text-amber-400">Pending</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if (! $lookupAccount->isApproved() && ! $lookupAccount->isBanned())
                                    <form method="POST" action="{{ route('support.approvals.approve', $lookupAccount) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-indigo-500">Approve</button>
                                    </form>
                                @endif
                                @if ($lookupAccount->isApproved() && ! $lookupAccount->is(auth()->user()))
                                    <form method="POST" action="{{ route('support.approvals.revoke', $lookupAccount) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-slate-100 dark:hover:bg-white/10">Revoke</button>
                                    </form>
                                @endif
                                @if (! $lookupAccount->isBanned() && ! $lookupAccount->is(auth()->user()))
                                    <form method="POST" action="{{ route('support.accounts.ban', $lookupAccount) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-red-500">Ban</button>
                                    </form>
                                @endif
                                @if ($lookupAccount->isBanned())
                                    <form method="POST" action="{{ route('support.accounts.unban', $lookupAccount) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-slate-100 dark:hover:bg-white/10">Unban</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Monitoring panel --}}
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-white/10 dark:bg-slate-900/80">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">Service Health</h3>
                </div>
                <div class="grid grid-cols-1 divide-y divide-gray-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0 dark:divide-white/10">

                    {{-- Queue depth --}}
                    <div class="px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">Queue depth</p>
                        <p class="mt-1 text-2xl font-semibold
                            {{ $monitoring['queue_depth'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $monitoring['queue_depth'] }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-slate-400">deliveries queued or sending</p>
                    </div>

                    {{-- Recent failures --}}
                    <div class="px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">Failures (24h)</p>
                        <p class="mt-1 text-2xl font-semibold
                            {{ count($monitoring['recent_failures']) > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ count($monitoring['recent_failures']) }}
                        </p>
                        @if (count($monitoring['recent_failures']) > 0)
                            <ul class="mt-2 space-y-1">
                                @foreach ($monitoring['recent_failures'] as $failure)
                                    <li class="text-xs text-gray-600 dark:text-slate-300">
                                        <span class="font-medium">{{ $failure->type }}</span>
                                        → {{ $failure->recipient }}
                                        @if ($failure->error_message)
                                            — <span class="text-red-600 dark:text-red-400">{{ $failure->error_message }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-slate-400">no failures</p>
                        @endif
                    </div>

                    {{-- Watcher health --}}
                    <div class="px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">Watcher last seen</p>
                        @if ($monitoring['last_payment_at'])
                            <p class="mt-1 text-sm font-semibold
                                {{ $monitoring['watcher_stale'] ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">
                                {{ \Illuminate\Support\Carbon::parse($monitoring['last_payment_at'])->setTimezone(config('app.timezone'))->toDayDateTimeString() }}
                            </p>
                            @if ($monitoring['watcher_stale'])
                                <p class="mt-0.5 text-xs text-amber-600 dark:text-amber-400">
                                    No activity in over {{ $monitoring['stale_minutes'] }} minutes — worth checking.
                                </p>
                            @else
                                <p class="mt-0.5 text-xs text-green-600 dark:text-green-400">recent</p>
                            @endif
                        @else
                            <p class="mt-1 text-sm text-gray-400 dark:text-slate-500">No on-chain payments recorded yet.</p>
                        @endif
                    </div>

                </div>
            </div>

            <div class="rounded-lg bg-white shadow dark:bg-slate-900/80">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-slate-900/90">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Issuer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Access expires</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Counts</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-slate-900/80">
                            @forelse ($issuers as $issuer)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $issuer->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">{{ $issuer->email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">{{ $issuer->support_access_expires_at?->setTimezone(config('app.timezone'))->toDayDateTimeString() }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">{{ $issuer->clients_count }} clients / {{ $issuer->invoices_count }} invoices</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('support.issuers.invoices.index', $issuer) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-slate-100 dark:hover:bg-white/10">Invoices</a>
                                            <a href="{{ route('support.issuers.clients.index', $issuer) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-slate-100 dark:hover:bg-white/10">Clients</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-slate-400">No active support grants right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $issuers->onEachSide(1)->links() }}</div>
        </div>
    </div>
</x-app-layout>
