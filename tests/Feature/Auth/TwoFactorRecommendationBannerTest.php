<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorRecommendationBannerTest extends TestCase
{
    use RefreshDatabase;

    private const BANNER = 'Add two-factor authentication';

    public function test_banner_shows_for_users_without_two_factor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertSee(self::BANNER);
    }

    public function test_banner_is_hidden_once_email_two_factor_is_enabled(): void
    {
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->actingAs($user)->get(route('dashboard'))->assertDontSee(self::BANNER);
    }

    public function test_banner_is_hidden_once_totp_is_enabled(): void
    {
        $user = User::factory()->create([
            'two_factor_totp_secret' => 'ABCDEFGHIJKLMNOP',
            'two_factor_totp_confirmed_at' => now(),
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertDontSee(self::BANNER);
    }

    public function test_banner_stays_dismissed_for_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.recommendation.dismiss'))->assertRedirect();
        $this->actingAs($user)->get(route('dashboard'))->assertDontSee(self::BANNER);
    }
}
