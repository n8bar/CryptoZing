<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_wallet_watch_payments_command_is_scheduled(): void
    {
        /** @var Schedule $schedule */
        Artisan::call('schedule:list');

        $schedule = $this->app->make(Schedule::class);

        $event = collect($schedule->events())
            ->first(fn (Event $event) => str_contains($event->command ?? '', 'wallet:watch-payments'));

        $this->assertNotNull($event, 'wallet:watch-payments should be scheduled.');
        $this->assertSame('* * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
        // #188: a stranded mutex must clear within one interval plus a margin, not the 24 h default.
        $this->assertSame(10, $event->expiresAt);
        $this->assertTrue($event->runInBackground);
    }
}
