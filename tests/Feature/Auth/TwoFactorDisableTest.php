<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use App\Services\TwoFactor\TwoFactorCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorDisableTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_disables_email_two_factor_with_a_valid_code(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->actingAs($user)->post(route('two-factor.email.disable-request'));
        $code = $this->capturedCode();

        $response = $this->actingAs($user)->delete(route('two-factor.email.disable'), ['code' => $code]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertFalse($user->hasEmailTwoFactorEnabled());
        $this->assertNull($user->two_factor_email_enabled_at);
    }

    public function test_disable_is_rejected_without_a_valid_code(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->actingAs($user)->post(route('two-factor.email.disable-request'));

        $response = $this->actingAs($user)->delete(route('two-factor.email.disable'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertTrue($user->hasEmailTwoFactorEnabled());
    }

    public function test_settings_page_shows_a_disable_control_when_enabled(): void
    {
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertSee('Disable two-factor');
    }

    public function test_settings_does_not_show_the_disable_form_from_a_stray_code(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        // A code minted by another flow (login / wallet step-up) sets the shared
        // column but not the settings disable-intent flag.
        app(TwoFactorCodeService::class)->sendCode($user);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertSee('Disable two-factor');       // plain enabled state
        $response->assertDontSee('Confirm and disable');  // no spurious code-entry form
    }

    public function test_requesting_disable_shows_the_code_form(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->actingAs($user)->post(route('two-factor.email.disable-request'));

        $this->actingAs($user)->get('/profile')->assertSee('Confirm and disable');
    }

    public function test_disable_endpoint_is_throttled(): void
    {
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)->delete(route('two-factor.email.disable'), ['code' => '000000']);
        }

        $this->actingAs($user)->delete(route('two-factor.email.disable'), ['code' => '000000'])
            ->assertStatus(429);
    }

    private function capturedCode(): string
    {
        $code = null;
        Mail::assertQueued(TwoFactorCodeMail::class, function (TwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }
}
