<?php

namespace App\Listeners;

use App\Support\WorkerHeartbeat;
use Illuminate\Queue\Events\Looping;

/**
 * Marks the queue worker alive on every turn of its loop, idle turns included,
 * so the container healthcheck can tell a working worker from a wedged one.
 */
class RefreshQueueHeartbeat
{
    /**
     * Looping is dispatched with until(): answering false would pause the
     * worker, so this returns nothing at all.
     */
    public function handle(Looping $event): void
    {
        WorkerHeartbeat::touch(WorkerHeartbeat::QUEUE);
    }
}
