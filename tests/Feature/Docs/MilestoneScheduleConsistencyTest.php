<?php

namespace Tests\Feature\Docs;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Guards the one invariant that kept drifting by hand: every milestone's
 * Target in PLAN.md must equal that milestone's end date in milestones.ics.
 *
 * Tagged local-only and excluded from the GitHub `PR Tests` gate
 * (see .github/workflows/pr-tests.yml). It runs as part of the normal local
 * suite — `./vendor/bin/sail artisan test` — so drift surfaces the moment you
 * edit one file without the other, without gating PRs on a docs invariant.
 */
#[Group('local-only')]
class MilestoneScheduleConsistencyTest extends TestCase
{
    public function test_plan_targets_match_ics_end_dates(): void
    {
        $icsEndDates = $this->icsEndDatesByMilestone();
        $planTargets = $this->planTargetsByMilestone();

        $this->assertNotEmpty($planTargets, 'No milestone rows parsed from PLAN.md active table.');

        foreach ($planTargets as $id => $target) {
            $this->assertArrayHasKey(
                $id,
                $icsEndDates,
                "PLAN.md lists MS{$id} but milestones.ics has no matching event."
            );

            $this->assertSame(
                $icsEndDates[$id],
                $target,
                "MS{$id}: PLAN.md Target ({$target}) != milestones.ics end date ({$icsEndDates[$id]})."
            );
        }
    }

    /** @return array<int,string> milestone number => end date (Y-m-d) */
    private function icsEndDatesByMilestone(): array
    {
        $ics = file_get_contents(base_path('docs/milestones.ics'));

        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $ics, $blocks);

        $endDates = [];

        foreach ($blocks[1] as $block) {
            if (! preg_match('/SUMMARY:MS(\d+)/', $block, $summary)) {
                continue;
            }

            if (! preg_match('/DTEND;VALUE=DATE:(\d{8})/', $block, $dtend)) {
                continue;
            }

            $endDates[(int) $summary[1]] = \DateTime::createFromFormat('Ymd', $dtend[1])->format('Y-m-d');
        }

        return $endDates;
    }

    /** @return array<int,string> milestone number => Target date (Y-m-d) */
    private function planTargetsByMilestone(): array
    {
        $plan = file_get_contents(base_path('docs/PLAN.md'));

        $section = Str::between($plan, '## Active and Upcoming Milestones', '## Completed Milestones');

        $targets = [];

        foreach (preg_split('/\R/', $section) as $line) {
            // Table rows look like: | [ ] | 19 | name | intent | 2026-07-18 | doc |
            if (! preg_match('/^\|\s*\[.?\]\s*\|/', $line)) {
                continue;
            }

            $cells = array_map('trim', explode('|', $line));

            // explode leaves an empty leading cell; columns: 1=status 2=id ... 5=Target
            if (! ctype_digit($cells[2] ?? '') || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $cells[5] ?? '')) {
                continue;
            }

            $targets[(int) $cells[2]] = $cells[5];
        }

        return $targets;
    }
}
