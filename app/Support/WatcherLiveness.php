<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * When the payment watcher last completed a full sweep.
 *
 * Lives in the cache (database store) because the watcher runs in the
 * scheduler container while the support dashboard renders in the app
 * container — WorkerHeartbeat's files are container-local by design and
 * cannot cross that boundary (#163).
 */
class WatcherLiveness
{
    public const CACHE_KEY = 'watcher:last_completed_run_at';

    public static function recordCompletedRun(): void
    {
        Cache::forever(self::CACHE_KEY, now()->toIso8601String());
    }

    public static function lastCompletedRunAt(): ?Carbon
    {
        $value = Cache::get(self::CACHE_KEY);

        return $value ? Carbon::parse($value) : null;
    }
}
