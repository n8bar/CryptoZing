<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WatcherLiveness;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $issuers = User::query()
            ->whereNotNull('support_access_granted_at')
            ->where('support_access_expires_at', '>', now())
            ->withCount(['clients', 'invoices'])
            ->orderBy('support_access_expires_at')
            ->paginate(15)
            ->withQueryString();

        // Alpha gate: accounts awaiting manual approval. Separate page name so
        // this paginator and the issuer list don't fight over ?page.
        $pendingApprovals = User::pending()
            ->orderBy('created_at')
            ->paginate(15, ['*'], 'pending_page')
            ->withQueryString();

        // Account lookup by email substring — the surface for ban/unban/revoke
        // on accounts that aren't in the pending list. Wildcards in the input
        // are literals, and results are capped with a narrow-the-search note.
        $lookupEmail = trim((string) $request->query('account', ''));
        $lookupAccounts = collect();
        $lookupOverflow = false;

        if ($lookupEmail !== '') {
            $matches = User::where('email', 'like', '%'.addcslashes($lookupEmail, '%_\\').'%')
                ->orderBy('email')
                ->limit(11)
                ->get();

            $lookupOverflow = $matches->count() > 10;
            $lookupAccounts = $matches->take(10);
        }

        return view('support.dashboard', [
            'issuers' => $issuers,
            'pendingApprovals' => $pendingApprovals,
            'lookupEmail' => $lookupEmail,
            'lookupAccounts' => $lookupAccounts,
            'lookupOverflow' => $lookupOverflow,
            'monitoring' => $this->monitoringPanel(),
        ]);
    }

    private function monitoringPanel(): array
    {
        $queueDepth = DB::table('invoice_deliveries')
            ->whereIn('status', ['queued', 'sending'])
            ->count();

        $recentFailures = DB::table('invoice_deliveries')
            ->where('status', 'failed')
            ->where('updated_at', '>=', now()->subHours(24))
            ->orderByDesc('updated_at')
            ->get(['type', 'recipient', 'error_message', 'updated_at']);

        $lastPaymentAt = DB::table('invoice_payments')
            ->where('is_adjustment', false)
            ->max(DB::raw('COALESCE(detected_at, created_at)'));

        // Liveness comes from the watcher's own run stamp, never from payment
        // recency — a quiet hour is not a dead watcher, and a payment landing
        // says nothing about the watcher since (#163). No stamp reads stale.
        $staleMinutes = config('support.watcher_stale_minutes', 60);
        $watcherLastRanAt = WatcherLiveness::lastCompletedRunAt();
        $watcherStale = $watcherLastRanAt === null
            || $watcherLastRanAt->lt(now()->subMinutes($staleMinutes));

        return [
            'queue_depth'         => $queueDepth,
            'recent_failures'     => $recentFailures,
            'last_payment_at'     => $lastPaymentAt,
            'watcher_last_ran_at' => $watcherLastRanAt,
            'watcher_stale'       => $watcherStale,
            'stale_minutes'       => $staleMinutes,
        ];
    }
}
