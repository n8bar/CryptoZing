<?php

namespace App\Support;

/**
 * Liveness marker for the worker containers.
 *
 * The queue and scheduler containers run workers, not PHP-FPM, so the image's
 * FPM ping cannot speak for them. Each worker refreshes its own heartbeat as
 * its loop turns, and the container healthcheck reads the age of that file —
 * which is what separates a worker that is running from one that is wedged.
 *
 * Deliberately framework-free: docker/production/worker-healthcheck.php loads
 * this class directly, without booting Laravel.
 */
class WorkerHeartbeat
{
    public const QUEUE = 'queue';

    public const SCHEDULER = 'scheduler';

    /**
     * Container-local by design — the healthcheck runs inside the container it
     * is judging, so a heartbeat left behind by some other host has no say.
     */
    public static function directory(): string
    {
        $configured = getenv('WORKER_HEARTBEAT_DIR');

        return is_string($configured) && trim($configured) !== ''
            ? rtrim(trim($configured), '/')
            : sys_get_temp_dir();
    }

    public static function path(string $worker): string
    {
        return self::directory().'/cz-heartbeat-'.$worker;
    }

    public static function touch(string $worker): void
    {
        @touch(self::path($worker));
    }

    /**
     * Seconds since the worker last reported, or null if it never has.
     */
    public static function ageInSeconds(string $worker): ?int
    {
        $path = self::path($worker);

        clearstatcache(true, $path);
        $modified = @filemtime($path);

        return $modified === false ? null : max(0, time() - $modified);
    }
}
