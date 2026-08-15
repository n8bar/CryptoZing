<?php

namespace Tests\Unit;

use App\Services\ConfirmationPolicy;
use PHPUnit\Framework\TestCase;

class ConfirmationPolicyTest extends TestCase
{
    public function test_required_confirmations_scale_with_usd_value(): void
    {
        $policy = new ConfirmationPolicy('500:1,5000:2,50000:3,*:6');

        $this->assertSame(1, $policy->requiredConfirmations(0.0));
        $this->assertSame(1, $policy->requiredConfirmations(499.99));
        $this->assertSame(2, $policy->requiredConfirmations(500.0));
        $this->assertSame(2, $policy->requiredConfirmations(4_999.99));
        $this->assertSame(3, $policy->requiredConfirmations(5_000.0));
        $this->assertSame(3, $policy->requiredConfirmations(49_999.99));
        $this->assertSame(6, $policy->requiredConfirmations(50_000.0));
        $this->assertSame(6, $policy->requiredConfirmations(1_000_000.0));
    }

    public function test_unknown_value_requires_the_top_tier(): void
    {
        $policy = new ConfirmationPolicy('500:1,5000:2,50000:3,*:6');

        $this->assertSame(6, $policy->requiredConfirmations(null));
    }

    public function test_minimum_required_is_the_fewest_tier(): void
    {
        $policy = new ConfirmationPolicy('500:1,5000:2,50000:3,*:6');

        $this->assertSame(1, $policy->minimumRequired());
    }

    public function test_custom_ladder_overrides_boundaries_and_counts(): void
    {
        $policy = new ConfirmationPolicy('100:2,*:4');

        $this->assertSame(2, $policy->requiredConfirmations(50.0));
        $this->assertSame(4, $policy->requiredConfirmations(100.0));
        $this->assertSame(2, $policy->minimumRequired());
    }

    public function test_malformed_ladder_falls_back_to_defaults(): void
    {
        $policy = new ConfirmationPolicy('not a ladder');

        $this->assertSame(1, $policy->requiredConfirmations(100.0));
        $this->assertSame(6, $policy->requiredConfirmations(60_000.0));
    }

    public function test_ladder_without_catch_all_still_covers_large_values(): void
    {
        $policy = new ConfirmationPolicy('500:1,5000:2');

        $this->assertSame(2, $policy->requiredConfirmations(999_999.0));
    }
}
