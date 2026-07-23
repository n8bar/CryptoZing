<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
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

    private function capturedCode(): string
    {
        $code = null;
        Mail::assertSent(TwoFactorCodeMail::class, function (TwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }
}
