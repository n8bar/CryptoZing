<?php

namespace Tests\Feature\Workers;

use App\Support\WorkerHeartbeat;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event as Events;
use Tests\TestCase;

class WorkerHeartbeatTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/cz-heartbeat-test-'.getmypid();
        @mkdir($this->dir, 0o777, true);
        putenv('WORKER_HEARTBEAT_DIR='.$this->dir);
    }

    protected function tearDown(): void
    {
        putenv('WORKER_HEARTBEAT_DIR');

        foreach (glob($this->dir.'/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->dir);

        parent::tearDown();
    }

    public function test_touch_writes_the_named_workers_heartbeat_file(): void
    {
        WorkerHeartbeat::touch(WorkerHeartbeat::QUEUE);

        $this->assertSame($this->dir.'/cz-heartbeat-queue', WorkerHeartbeat::path(WorkerHeartbeat::QUEUE));
        $this->assertFileExists($this->dir.'/cz-heartbeat-queue');
    }

    public function test_each_worker_keeps_its_own_heartbeat(): void
    {
        WorkerHeartbeat::touch(WorkerHeartbeat::SCHEDULER);

        $this->assertFileExists($this->dir.'/cz-heartbeat-scheduler');
        $this->assertFileDoesNotExist($this->dir.'/cz-heartbeat-queue');
    }

    public function test_age_reports_how_long_ago_the_worker_last_reported(): void
    {
        WorkerHeartbeat::touch(WorkerHeartbeat::QUEUE);
        touch(WorkerHeartbeat::path(WorkerHeartbeat::QUEUE), time() - 90);

        $this->assertSame(90, WorkerHeartbeat::ageInSeconds(WorkerHeartbeat::QUEUE));
    }

    public function test_age_is_null_when_the_worker_has_never_reported(): void
    {
        $this->assertNull(WorkerHeartbeat::ageInSeconds(WorkerHeartbeat::QUEUE));
    }

    public function test_a_turn_of_the_queue_worker_loop_refreshes_the_heartbeat_without_pausing_the_worker(): void
    {
        // Looping is a halting dispatch: a listener that answers false stops
        // the worker taking work, so the heartbeat must stay out of the way.
        $answer = Events::until(new Looping('database', 'default'));

        $this->assertFileExists(WorkerHeartbeat::path(WorkerHeartbeat::QUEUE));
        $this->assertNotFalse($answer);
    }

    public function test_the_scheduler_heartbeat_runs_every_minute(): void
    {
        $event = $this->scheduledHeartbeat();

        $this->assertNotNull($event, 'The scheduler heartbeat should be scheduled.');
        $this->assertSame('* * * * *', $event->expression);
    }

    public function test_running_the_scheduled_heartbeat_refreshes_the_scheduler_heartbeat(): void
    {
        $this->scheduledHeartbeat()->run($this->app);

        $this->assertFileExists(WorkerHeartbeat::path(WorkerHeartbeat::SCHEDULER));
    }

    private function scheduledHeartbeat(): ?Event
    {
        // The schedule closure only runs once a console command resolves it.
        Artisan::call('schedule:list');

        return collect($this->app->make(Schedule::class)->events())
            ->first(fn (Event $event) => $event->description === 'worker-heartbeat');
    }
}
