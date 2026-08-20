<?php

namespace Tests\Feature\Workers;

use App\Support\WorkerHeartbeat;
use Tests\TestCase;

/**
 * Covers docker/production/worker-healthcheck.php, which the queue and
 * scheduler containers run in place of the image's PHP-FPM ping.
 */
class WorkerHealthcheckScriptTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/cz-healthcheck-test-'.getmypid();
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

    public function test_a_worker_reporting_within_the_window_is_healthy(): void
    {
        WorkerHeartbeat::touch(WorkerHeartbeat::QUEUE);

        [$status] = $this->check(WorkerHeartbeat::QUEUE, 60);

        $this->assertSame(0, $status);
    }

    public function test_a_worker_that_stopped_reporting_is_unhealthy(): void
    {
        WorkerHeartbeat::touch(WorkerHeartbeat::QUEUE);
        touch(WorkerHeartbeat::path(WorkerHeartbeat::QUEUE), time() - 300);

        [$status, $output] = $this->check(WorkerHeartbeat::QUEUE, 60);

        $this->assertSame(1, $status);
        $this->assertStringContainsString('past the 60s window', $output);
    }

    public function test_a_worker_that_never_reported_is_unhealthy(): void
    {
        [$status, $output] = $this->check(WorkerHeartbeat::SCHEDULER, 180);

        $this->assertSame(1, $status);
        $this->assertStringContainsString('no heartbeat', $output);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function check(string $worker, int $maxAge): array
    {
        $command = sprintf(
            'WORKER_HEARTBEAT_DIR=%s php %s %s %d 2>&1',
            escapeshellarg($this->dir),
            escapeshellarg(base_path('docker/production/worker-healthcheck.php')),
            escapeshellarg($worker),
            $maxAge,
        );

        exec($command, $output, $status);

        return [$status, implode("\n", $output)];
    }
}
