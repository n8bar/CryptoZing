<?php

namespace Tests\Feature\Workers;

use App\Support\WorkerHeartbeat;
use Tests\TestCase;

/**
 * The queue and scheduler services reuse the app image through a YAML anchor,
 * so anything they do not declare they inherit — including the image's PHP-FPM
 * healthcheck, which neither of them can ever answer (#153).
 */
class ProductionComposeHealthcheckTest extends TestCase
{
    public function test_the_worker_services_answer_on_their_own_heartbeats(): void
    {
        $this->assertSame(
            [WorkerHeartbeat::QUEUE, WorkerHeartbeat::SCHEDULER],
            array_column($this->declaredHealthchecks(), 'worker'),
        );
    }

    public function test_the_healthcheck_the_compose_file_names_is_in_the_image(): void
    {
        $checks = $this->declaredHealthchecks();
        $this->assertNotEmpty($checks);

        foreach ($checks as $check) {
            $this->assertFileExists(
                base_path(ltrim(str_replace('/var/www/html', '', $check['script']), '/')),
            );
        }
    }

    /**
     * @return list<array{script: string, worker: string}>
     */
    private function declaredHealthchecks(): array
    {
        preg_match_all(
            '#"(/var/www/html/\S+/worker-healthcheck\.php)",\s*"([a-z]+)"#',
            (string) file_get_contents(base_path('compose.production.yaml')),
            $matches,
            PREG_SET_ORDER,
        );

        return array_map(
            fn (array $match) => ['script' => $match[1], 'worker' => $match[2]],
            $matches,
        );
    }
}
