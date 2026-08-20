<?php

/**
 * Container healthcheck for the queue and scheduler services.
 *
 * Those containers run workers rather than PHP-FPM, so the image's FPM ping
 * says nothing about them. Each worker refreshes a heartbeat as its loop
 * turns; this reads the age of that heartbeat, which is what separates a
 * worker that is still working from one that is wedged.
 *
 *   php worker-healthcheck.php <worker> <max-age-seconds>
 *
 * Loads the heartbeat class directly — no framework boot on a 30s interval.
 */

use App\Support\WorkerHeartbeat;

require __DIR__.'/../../app/Support/WorkerHeartbeat.php';

$worker = $argv[1] ?? null;
$maxAge = isset($argv[2]) ? (int) $argv[2] : 0;

if ($worker === null || $maxAge <= 0) {
    fwrite(STDERR, "usage: worker-healthcheck.php <worker> <max-age-seconds>\n");
    exit(2);
}

$age = WorkerHeartbeat::ageInSeconds($worker);

if ($age === null) {
    fwrite(STDERR, "no heartbeat at ".WorkerHeartbeat::path($worker)."\n");
    exit(1);
}

if ($age > $maxAge) {
    fwrite(STDERR, "{$worker} last reported {$age}s ago, past the {$maxAge}s window\n");
    exit(1);
}

echo "{$worker} last reported {$age}s ago\n";
exit(0);
